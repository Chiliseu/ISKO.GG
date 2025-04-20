<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ISKOGG - Game Guide</title>

    <!-- External CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

    <!-- Background Effects -->
    <div class="falling-container"></div>
    <div class="wave-effect"></div>

    <!-- Title -->
    <div class="title">ISKO.GG</div>
    <div class="subtitle">AI-POWERED GAME SUGGESTION AND LORE SUMMARIZER</div>

    <!-- Input -->
    <div class="input-box">
        <input type="text" id="game-input" placeholder="What games do you like?" aria-label="Game input">
    </div>

    <!-- Buttons -->
    <div class="button-container">
        <button class="button" id="summarize-button">Summarize</button>
        <button class="button" id="recommend-button">Recommend</button>
    </div>

    <!-- Game Cards -->
    <div id="recommendation-container" class="recommendation-container"></div>

    <!-- Robot Facts -->
    <div class="robot-fact-box" id="robot-fact-box">
        <img id="robot-image" src="{{ asset('images/marchfacts1.png') }}" alt="Robot showing game facts">
        <span id="game-fact">Loading...</span>
    </div>

    <!-- Music -->
    <audio id="background-music" autoplay loop>
        <source src="{{ asset('music/LittleRoot.mp3') }}" type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>
    <button class="music-toggle" id="music-toggle" aria-label="Toggle music">🔊</button>

    <!-- Arrows -->
    <div class="recommendation-wrapper">
        <button id="scroll-left" class="scroll-btn" aria-label="Scroll left">◀</button>
        <button id="scroll-right" class="scroll-btn" aria-label="Scroll right">▶</button>
    </div>

    <!-- Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const music = document.getElementById("background-music");
            const musicToggle = document.getElementById("music-toggle");
            const recommendButton = document.getElementById("recommend-button");
            const recommendationContainer = document.getElementById("recommendation-container");
            const scrollLeft = document.getElementById("scroll-left");
            const scrollRight = document.getElementById("scroll-right");
            let isPlaying = true;

            musicToggle.addEventListener("click", () => {
                isPlaying ? music.pause() : music.play().catch(() => {});
                musicToggle.innerText = isPlaying ? "🔇" : "🔊";
                isPlaying = !isPlaying;
            });

            // Falling shapes
            setInterval(() => {
                const container = document.querySelector('.falling-container');
                const waveContainer = document.querySelector('.wave-effect');
                for (let i = 0; i < 5; i++) {
                    let shape = document.createElement('div');
                    shape.classList.add('falling-shape', `shape-${Math.floor(Math.random() * 7) + 1}`);
                    const size = Math.random() * 40 + 20;
                    shape.style.width = shape.style.height = `${size}px`;
                    shape.style.left = `${Math.random() * 100}vw`;
                    shape.style.top = '-50px';
                    container.appendChild(shape);

                    (function fall() {
                        let currentY = parseFloat(shape.style.top);
                        shape.style.top = `${currentY + 2}px`;
                        if (currentY + size >= window.innerHeight - 10) {
                            shape.classList.add("pop-effect");
                            let wave = document.createElement('div');
                            wave.classList.add('wave');
                            wave.style.left = shape.style.left;
                            wave.style.backgroundColor = getComputedStyle(shape).backgroundColor;
                            waveContainer.appendChild(wave);
                            setTimeout(() => {
                                shape.remove();
                                wave.remove();
                            }, 500);
                        } else {
                            requestAnimationFrame(fall);
                        }
                    })();
                }
            }, 1000);

            // Facts
            const facts = [
                "Pac-Man was originally called 'Puck Man'.",
                "Super Mario 64 pioneered full 3D movement.",
                "Minecraft was created in just 6 days!",
                "The PlayStation 2 is the best-selling console ever.",
                "Tetris was invented by a Soviet software engineer.",
                "The longest gaming session recorded lasted 35 hours!"
            ];
            const robotImages = [
                "{{ asset('images/marchfacts.png') }}",
                "{{ asset('images/marchfacts2.png') }}",
                "{{ asset('images/marchfacts3.png') }}",
                "{{ asset('images/marchfacts4.png') }}"
            ];

            setInterval(() => {
                const factText = document.getElementById("game-fact");
                const robotImage = document.getElementById("robot-image");
                factText.innerText = facts[Math.floor(Math.random() * facts.length)];
                robotImage.src = robotImages[Math.floor(Math.random() * robotImages.length)];
                document.getElementById("robot-fact-box").classList.add("show-fact");
                setTimeout(() => document.getElementById("robot-fact-box").classList.remove("show-fact"), 5000);
            }, 7000);

            function extractYouTubeID(url) {
                try {
                    const regExp = /(?:youtube\.com\/.*[?&]v=|youtu\.be\/)([^&\n?#]+)/;
                    const match = url.match(regExp);
                    return match ? match[1] : null;
                } catch {
                    return null;
                }
            }

            function getCsrfToken() {
                return document.querySelector('meta[name="csrf-token"]').getAttribute('content') || '';
            }

            recommendButton.addEventListener("click", () => {
                const input = document.getElementById("game-input").value.trim();
                const csrfToken = getCsrfToken();
                if (!input) return alert("Please enter a game before requesting recommendations.");
                if (!csrfToken) return alert("CSRF Token missing. Try refreshing the page.");

                fetch("{{ route('get.recommendation') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrfToken,
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ game: input })
                })
                .then(res => res.json())
                .then(data => {
                    recommendationContainer.innerHTML = "";
                    const games = data.games;
                    if (games.length === 0 || games[0].name.includes("No recommendations")) {
                        recommendationContainer.innerHTML = "<p>No recommendations found. Try a different keyword!</p>";
                        scrollLeft.style.display = "none";
                        scrollRight.style.display = "none";
                        return;
                    }

                    games.forEach(game => {
                        const card = document.createElement("div");
                        card.classList.add("landscape-card");

                        const isYouTube = game.trailer?.includes("youtube") || game.trailer?.includes("youtu.be");
                        const ytID = extractYouTubeID(game.trailer);
                        const trailerEmbed = isYouTube && ytID 
                            ? `<iframe class="landscape-trailer" src="https://www.youtube.com/embed/${ytID}?autoplay=1&mute=1&loop=1&playlist=${ytID}" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>`
                            : game.trailer 
                                ? `<video class="landscape-trailer" muted loop preload="none"><source src="${game.trailer}" type="video/mp4"></video>`
                                : `<div class="no-trailer-overlay"></div>`;

                        card.innerHTML = `
                            <div class="media-container">
                                <img src="${game.image}" alt="${game.name}" class="landscape-image">
                                ${trailerEmbed}
                                <div class="landscape-info">
                                    <h3>${game.name}</h3>
                                    <p><strong>Rating:</strong> ${game.rating}</p>
                                    <p><strong>Platforms:</strong> ${game.platforms}</p>
                                    <p><strong>Genres:</strong> ${game.genres}</p>
                                    <a href="${game.url}" target="_blank">View on RAWG</a>
                                </div>
                            </div>
                        `;

                        recommendationContainer.appendChild(card);

                        if (!isYouTube && game.trailer) {
                            const video = card.querySelector("video");
                            card.addEventListener("mouseenter", () => video?.play());
                            card.addEventListener("mouseleave", () => {
                                video?.pause();
                                video.currentTime = 0;
                            });
                        }
                    });

                    setTimeout(() => {
                        const overflow = recommendationContainer.scrollWidth > recommendationContainer.clientWidth;
                        scrollLeft.style.display = overflow ? "block" : "none";
                        scrollRight.style.display = overflow ? "block" : "none";
                    }, 100);
                })
                .catch(() => alert("An error occurred. Please try again later."));
            });

            scrollLeft.addEventListener("click", () => {
                const card = document.querySelector(".landscape-card");
                if (card) {
                    const scrollAmount = (card.offsetWidth + 10) * 2;
                    recommendationContainer.scrollBy({ left: -scrollAmount, behavior: "smooth" });
                }
            });

            scrollRight.addEventListener("click", () => {
                const card = document.querySelector(".landscape-card");
                if (card) {
                    const scrollAmount = (card.offsetWidth + 10) * 2;
                    recommendationContainer.scrollBy({ left: scrollAmount, behavior: "smooth" });
                }
            });
        });
    </script>
</body>
</html>
