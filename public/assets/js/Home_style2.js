// Wishlist toggle interactions on the home page.
// Each button calls a JSON endpoint and updates its icon/state.
document.addEventListener("DOMContentLoaded", function() {
    const wishlistButtons = document.querySelectorAll('.toggle-wishlist');

    wishlistButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            // Read data attributes configured in the HTML.
            const offerId = this.getAttribute('data-id');
            const action = this.getAttribute('data-action'); 
            
            // Decide which endpoint to call based on current state.
            const url = action === 'add' ? '/addWishlist?Id_offre=' + offerId : '/deleteWishlist?Id_offre=' + offerId;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const icon = this.querySelector('i');

                        if (action === 'add') {
                            // Switch to "delete" mode and show the active heart.
                            this.setAttribute('data-action', 'delete');
                            this.classList.add('active');
                            icon.classList.remove('fa-regular');
                            icon.classList.add('fa-solid');
                        } 
                        else {
                            // Switch back to "add" mode and show the empty heart.
                            this.setAttribute('data-action', 'add');
                            this.classList.remove('active');
                            icon.classList.remove('fa-solid');
                            icon.classList.add('fa-regular');
                        }
                    } else {
                        // Server rejected the action (unauthorized or invalid id).
                        console.error("Erreur lors de la modification des favoris.");
                    }
                })
                // Network / parsing errors.
                .catch(error => console.error("Erreur AJAX:", error));
        });
    });
});