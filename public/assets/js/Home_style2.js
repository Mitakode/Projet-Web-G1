document.addEventListener("DOMContentLoaded", function() {
    const wishlistButtons = document.querySelectorAll('.toggle-wishlist');

    wishlistButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            const offerId = this.getAttribute('data-id');
            const action = this.getAttribute('data-action'); 
            
            const url = action === 'add' ? '/addWishlist?Id_offre=' + offerId : '/deleteWishlist?Id_offre=' + offerId;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const icon = this.querySelector('i');

                        if (action === 'add') {
                            this.setAttribute('data-action', 'delete');
                            this.classList.add('active');
                            icon.classList.remove('fa-regular');
                            icon.classList.add('fa-solid');
                        } 
                        else {
                            this.setAttribute('data-action', 'add');
                            this.classList.remove('active');
                            icon.classList.remove('fa-solid');
                            icon.classList.add('fa-regular');
                        }
                    } else {
                        console.error("Erreur lors de la modification des favoris.");
                    }
                })
                .catch(error => console.error("Erreur AJAX:", error));
        });
    });
});