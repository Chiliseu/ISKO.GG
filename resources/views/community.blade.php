<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ISKO.GG | Game Community</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/community.css') }}">

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

    <style>
        /* CSS styles for the stars and ratings */
        .star-rating {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .star {
            font-size: 30px;
            color: #ccc;
            cursor: pointer;
        }

        .star.filled {
            color: #ff0; /* Yellow color for filled stars */
            text-shadow: 0 0 10px #ff0; /* Glowing effect */
        }

        .star.filled-5 {
            animation: pulse 1s ease-in-out infinite;
        }

        .comment-rating {
            display: flex;
            margin-top: 5px;
        }

        .comment-rating .star {
            font-size: 20px;
        }
    </style>
</head>
<body>
    <div class="animated-bg"></div>

    <div class="container">
        <div class="title">Game Community</div>

        <div class="search-box" style="position: relative;">
            <input type="text" id="gameSearch" placeholder="Search for a game..." oninput="liveSearch()" autocomplete="off">
            <div id="suggestions" class="suggestions" style="display: none;"></div>
        </div>

        @isset($game)
        <div id="gameInfo" class="game-info" data-game="{{ $game['name'] }}">
            <h2>{{ $game['name'] }}</h2>

            <img src="{{ $game['background_image'] ?? '' }}" alt="{{ $game['name'] }}" style="width: 1000px; height: auto;">
            <p class="game-detail"><strong>Rating:</strong> {{ $game['rating'] ?? 'N/A' }} ★</p>
            <p class="game-detail"><strong>Genres:</strong> {{ implode(', ', array_map(fn($g) => $g['name'], $game['genres'] ?? [])) }}</p>
            <p class="game-detail"><strong>Platforms:</strong> {{ implode(', ', array_map(fn($p) => $p['platform']['name'], $game['platforms'] ?? [])) }}</p>

            @php
                $trailer = $game['clip']['clip'] ?? null;
            @endphp

            @if ($trailer)
                <iframe width="100%" height="315" src="{{ $trailer }}" frameborder="0" allowfullscreen></iframe>
            @endif

            <!-- Star Rating System -->
            <div class="star-rating" id="ratingStars">
                <span class="star" data-value="1">★</span>
                <span class="star" data-value="2">★</span>
                <span class="star" data-value="3">★</span>
                <span class="star" data-value="4">★</span>
                <span class="star" data-value="5">★</span>
            </div>

            <div class="write-comment">
                <label for="commentText">Write a Comment:</label>
                <textarea id="commentText" rows="3" placeholder="Write a comment..."></textarea>
                <button onclick="submitComment()">Submit</button>
            </div>

            <div class="comments-section" id="commentsList"></div>
        </div>
        @endisset
    </div>

    <script>
        let currentGame = '';
        let currentRating = 0;

        const gameInfo = document.getElementById('gameInfo');
        if (gameInfo && gameInfo.dataset.game) {
            currentGame = gameInfo.dataset.game;
            loadComments();
        }

        function liveSearch() {
            const query = document.getElementById('gameSearch').value.trim();
            const suggestionsBox = document.getElementById('suggestions');

            if (query.length < 2) {
                suggestionsBox.style.display = 'none';
                return;
            }

            fetch(`/community/search?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.length > 0) {
                        suggestionsBox.innerHTML = '';
                        data.forEach(game => {
                            const div = document.createElement('div');
                            div.textContent = game.name;
                            div.onclick = () => {
                                window.location.href = `/community/${game.name.toLowerCase().replace(/\s+/g, '-')}`;
                            };
                            suggestionsBox.appendChild(div);
                        });
                        suggestionsBox.style.display = 'block';
                    } else {
                        suggestionsBox.style.display = 'none';
                    }
                })
                .catch(err => {
                    console.error('Live search error:', err);
                    suggestionsBox.style.display = 'none';
                });
        }

        function loadComments() {
            const commentsList = document.getElementById('commentsList');
            if (!currentGame) return;

            const data = JSON.parse(localStorage.getItem('comments_' + currentGame)) || [];
            commentsList.innerHTML = '';

            data.forEach((comment, index) => {
                const div = document.createElement('div');
                div.className = 'comment';
                
                div.innerHTML = `
                    <div class="comment-header">
                        <img class="avatar" src="/images/user1.png" alt="Avatar">
                        <strong class="comment-username">Player${index + 1}</strong>
                        <span class="user-badge">Level ${Math.floor(Math.random() * 20) + 1}</span>
                    </div>
                    <p class="comment-text">${comment.text}</p>
                    <div class="comment-rating">
                        ${generateStarRating(comment.rating)}
                    </div>
                    <small class="comment-time">Posted just now</small>
                    <div class="controls">
                        <span class="vote">⬆️ ${Math.floor(Math.random() * 20)}</span>
                        <span class="vote">⬇️</span>
                        <span class="reply">💬 Reply</span>
                    </div>
                `;
                commentsList.appendChild(div);
            });

            highlightStars(0);
        }

        function generateStarRating(rating) {
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                stars += `<span class="star ${i <= rating ? 'filled' : ''}">★</span>`;
            }
            return stars;
        }

        function submitComment() {
            const commentText = document.getElementById('commentText').value.trim();
            if (!commentText || currentRating === 0) {
                alert("Please write a comment and select a star rating.");
                return;
            }

            const existing = JSON.parse(localStorage.getItem('comments_' + currentGame)) || [];
            existing.push({ text: commentText, rating: currentRating });

            localStorage.setItem('comments_' + currentGame, JSON.stringify(existing));
            document.getElementById('commentText').value = '';
            currentRating = 0;
            loadComments();
        }

        function highlightStars(rating) {
            const ratingDiv = document.getElementById('ratingStars');
            ratingDiv.querySelectorAll('.star').forEach(star => {
                const starValue = parseInt(star.getAttribute('data-value'));
                if (starValue <= rating) {
                    star.classList.add('filled');
                    star.classList.remove('filled-5');
                } else {
                    star.classList.remove('filled');
                }
            });

            if (rating === 5) {
                ratingDiv.querySelectorAll('.star').forEach(star => star.classList.add('filled-5'));
            } else {
                ratingDiv.querySelectorAll('.star').forEach(star => star.classList.remove('filled-5'));
            }
        }

        document.querySelectorAll('.star-rating .star').forEach(star => {
            star.addEventListener('mouseover', () => {
                const value = parseInt(star.getAttribute('data-value'));
                highlightStars(value);
            });

            star.addEventListener('click', () => {
                currentRating = parseInt(star.getAttribute('data-value'));
                highlightStars(currentRating);
            });
        });
    </script>
</body>
</html>
