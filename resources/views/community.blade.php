<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ISKO.GG | Game Community</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/community.css') }}">
</head>
<body>
    <div class="container">
        <h1>🎮 Game Community</h1>

        <div class="search-box" style="position: relative;">
            <input type="text" id="gameSearch" placeholder="Search for a game..." oninput="liveSearch()" autocomplete="off">
            <div id="suggestions" class="suggestions" style="display: none;"></div>
        </div>

        @isset($game)
        <div id="gameInfo" class="game-info" data-game="{{ $game['name'] }}">
            <h2>{{ $game['name'] }}</h2>

            <img src="{{ $game['background_image'] ?? '' }}" alt="{{ $game['name'] }}" style="width: 200px; height: auto;">
            <p><strong>Rating:</strong> {{ $game['rating'] ?? 'N/A' }} ★</p>
            <p><strong>Genres:</strong> {{ implode(', ', array_map(fn($g) => $g['name'], $game['genres'] ?? [])) }}</p>
            <p><strong>Platforms:</strong> {{ implode(', ', array_map(fn($p) => $p['platform']['name'], $game['platforms'] ?? [])) }}</p>

            @php
                $trailer = $game['clip']['clip'] ?? null;
            @endphp

            @if ($trailer)
                <iframe width="100%" height="315" src="{{ $trailer }}" frameborder="0" allowfullscreen></iframe>
            @endif

            <div class="star-rating" id="ratingStars">
                ★★★★★
            </div>

            <textarea id="commentText" rows="3" placeholder="Write a comment..."></textarea>
            <button onclick="submitComment()">Submit</button>

            <div class="comments-section" id="commentsList"></div>
        </div>
        @endisset
    </div>

    <script>
        let currentGame = '';
        let currentRating = 0;

        // Safe DOM-based assignment
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
            <div style="display: flex; align-items: center; gap: 10px;">
                <img class="avatar" src="/images/user1.png" alt="Avatar">
                <strong>Player${index + 1}</strong><span class="user-badge">Level ${Math.floor(Math.random() * 20) + 1}</span>
            </div>
            <p>${comment.text}</p>
            <small>Posted just now</small>
            <div class="controls">
                <span>⬆️ ${Math.floor(Math.random() * 20)}</span>
                <span>⬇️</span>
                <span>💬 Reply</span>
            </div>
        `;
        commentsList.appendChild(div);
    });

    highlightStars(0);
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
            ratingDiv.innerHTML = '';
            for (let i = 1; i <= 5; i++) {
                const star = document.createElement('span');
                star.textContent = '★';
                star.className = i <= rating ? 'filled' : '';
                star.onclick = () => {
                    currentRating = i;
                    highlightStars(currentRating);
                };
                ratingDiv.appendChild(star);
            }
        }
    </script>
</body>
</html>
