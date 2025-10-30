import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    const voteForms = document.querySelectorAll('.js-vote-form');

    voteForms.forEach(form => {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            const voteType = this.querySelector('input[name="vote_type"]').value;
            const csrfToken = this.querySelector('input[name="_token"]').value;
            

            const reviewId = this.closest('.js-vote-container').dataset.reviewId;
            const actionUrl = `/reviews/${reviewId}/vote`;

            fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    vote_type: voteType
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const container = this.closest('.js-vote-container');
                    container.querySelector('.js-upvote-count').textContent = data.upvotes;
                    container.querySelector('.js-downvote-count').textContent = data.downvotes;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    });
});