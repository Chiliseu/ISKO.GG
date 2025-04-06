<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ISKOGG - Game Guide</title>

    <!-- Link to External CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <!-- Font Awesome for Icons -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

    <!-- Background Falling Shapes -->
    <div class="falling-container"></div>
    <div class="wave-effect"></div>

    <!-- Title -->
    <div class="title">ISKO.GG</div>
    <div class="subtitle">AI-POWERED GAME SUGGESTION AND LORE SUMMARIZER</div>

    <!-- Input Field -->
    <div class="input-box">
        <input type="text" id="game-input" placeholder="What games do you like?">
    </div>

    <!-- Buttons -->
    <div class="button-container">
        <button class="button">Summarize</button>
        <button class="button" id="recommend-button">Recommend</button>
    </div>

    <!-- Recommendation Container (NEWLY ADDED) -->
    <div id="recommendation-container" class="recommendation-container"></div>

    <!-- Robot Game Fact Box -->
    <div class="robot-fact-box" id="robot-fact-box">
        <img id="robot-image" src="{{ asset('images/marchfacts1.png') }}" alt="Robot">
        <span id="game-fact">Loading...</span>
    </div>

    <!-- Music Audio Element -->
    <audio id="background-music" autoplay>
        <source src="{{ asset('music/AnimalCrossing.mp3') }}" type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>

    <!-- Music Toggle Button -->
    <button class="music-toggle" id="music-toggle">🔊</button>

     <!-- LEFT AND RIGHT ARROW -->
    <div class="recommendation-wrapper">
    <button id="scroll-left" class="scroll-btn">◀</button>
    <button id="scroll-right" class="scroll-btn">▶</button>
    </div>


    <!-- JavaScript -->
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const music = document.getElementById("background-music");
        const musicToggle = document.getElementById("music-toggle");
        const recommendButton = document.getElementById("recommend-button");
        const recommendationContainer = document.getElementById("recommendation-container");
        const scrollLeft = document.getElementById("scroll-left");
        const scrollRight = document.getElementById("scroll-right");
        let isPlaying = true;

        function toggleMusic() {
            if (isPlaying) {
                music.pause();
                musicToggle.innerText = "🔇";
            } else {
                music.play().catch(error => console.log("Autoplay blocked:", error));
                musicToggle.innerText = "🔊";
            }
            isPlaying = !isPlaying;
        }

        musicToggle.addEventListener("click", toggleMusic);

        function createFallingShapes() {
            const container = document.querySelector('.falling-container');
            const waveContainer = document.querySelector('.wave-effect');

            for (let i = 0; i < 5; i++) {
                let shape = document.createElement('div');
                shape.classList.add('falling-shape');

                let shapeType = Math.floor(Math.random() * 7) + 1;
                shape.classList.add(`shape-${shapeType}`);

                let size = Math.random() * 40 + 20;
                shape.style.width = `${size}px`;
                shape.style.height = `${size}px`;
                shape.style.left = Math.random() * 100 + 'vw';
                shape.style.top = '-50px';

                container.appendChild(shape);

                function fall() {
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
                }

                fall();
            }
        }

        setInterval(createFallingShapes, 1000);

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

        function showRandomFact() {
            const factBox = document.getElementById("robot-fact-box");
            const factText = document.getElementById("game-fact");
            const robotImage = document.getElementById("robot-image");

            const randomFactIndex = Math.floor(Math.random() * facts.length);
            factText.innerText = facts[randomFactIndex];

            const randomImageIndex = Math.floor(Math.random() * robotImages.length);
            robotImage.src = robotImages[randomImageIndex];

            factBox.classList.add("show-fact");

            setTimeout(() => factBox.classList.remove("show-fact"), 5000);
        }

        setInterval(showRandomFact, 7000);

        // Fix CSRF Token Issue
        function getCsrfToken() {
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            return tokenMeta ? tokenMeta.getAttribute('content') : null;
        }

        // Fetch Game Recommendations
        recommendButton.addEventListener("click", function() {
            let input = document.getElementById("game-input").value.trim();

            if (!input) {
                alert("Please enter a game before requesting recommendations.");
                return;
            }

            let csrfToken = getCsrfToken();
            if (!csrfToken) {
                alert("CSRF Token missing. Try refreshing the page.");
                return;
            }

            fetch("{{ route('get.recommendation') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ game: input })
            })
            .then(response => response.json())
            .then(data => {
                recommendationContainer.innerHTML = ""; // Clear previous results

                if (data.games.length > 0 && data.games[0].name !== "No recommendations found. Try a different keyword!") {
                    data.games.forEach(game => {
                        let gameDiv = document.createElement("div");
                        gameDiv.classList.add("game-card");

                        gameDiv.innerHTML = `
                            <img src="${game.image || 'default-image.jpg'}" alt="${game.name}" class="game-image">
                            <h3>${game.name}</h3>
                            <p><strong>Rating:</strong> ${game.rating}</p>
                            <p><strong>Platforms:</strong> ${game.platforms}</p>
                            <p><strong>Genre:</strong> ${game.genres}</p>
                        `;

                        recommendationContainer.appendChild(gameDiv);
                    });
                    // **Check if scrolling is needed and show/hide arrows accordingly**
                    setTimeout(() => {
                        if (recommendationContainer.scrollWidth > recommendationContainer.clientWidth) {
                            scrollLeft.style.display = "block";
                            scrollRight.style.display = "block";
                        } else {
                            scrollLeft.style.display = "none";
                            scrollRight.style.display = "none";
                        }
                    }, 100);

                } else {
                    recommendationContainer.innerHTML = "<p>No recommendations found. Try a different keyword!</p>";
                    scrollLeft.style.display = "none";  
                    scrollRight.style.display = "none";
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred. Please try again later.");
            });
        });
           // Scroll multiple cards at once
           scrollLeft.addEventListener("click", () => {
                const card = document.querySelector(".game-card");
                if (!card) return; // Prevent errors if no cards exist

                const cardWidth = card.offsetWidth + 10; // Add margin if needed
                const scrollAmount = cardWidth * 5; // Move exactly 5 full cards
                const newScrollPosition = recommendationContainer.scrollLeft - scrollAmount;

                recommendationContainer.scrollTo({ left: newScrollPosition, behavior: "smooth" });
            });

            scrollRight.addEventListener("click", () => {
                const card = document.querySelector(".game-card");
                if (!card) return;

                const cardWidth = card.offsetWidth + 10;
                const scrollAmount = cardWidth * 5;
                const newScrollPosition = recommendationContainer.scrollLeft + scrollAmount;

                recommendationContainer.scrollTo({ left: newScrollPosition, behavior: "smooth" });
            });
    });
    </script>
</body>
</html>
