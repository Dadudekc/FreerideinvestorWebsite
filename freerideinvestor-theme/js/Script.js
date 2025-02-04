// Handle profile form submission
document.addEventListener('DOMContentLoaded', function () {
    const editProfileForm = document.getElementById('editProfileForm');

    if (editProfileForm) {
        editProfileForm.addEventListener('submit', function (event) {
            event.preventDefault(); // Prevents the page from reloading

            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();

            // Validate fields
            if (!name || !email) {
                alert('Please fill out all required fields.');
                return;
            }

            // Prepare the payload for submission
            const payload = {
                name: name,
                email: email,
                password: password, // Include password if it's required
            };

            // Example: Sending data to the server
            fetch('/profile/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        alert(`Profile updated successfully!\nName: ${name}\nEmail: ${email}`);
                    } else {
                        alert('Error updating profile. Please try again.');
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert('An unexpected error occurred.');
                });
        });
    }
});
