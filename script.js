// Add click event listeners to nav links

document.addEventListener('DOMContentLoaded', function() {
    // Select all nav links and dropdown items
    const navLinks = document.querySelectorAll('.nav-link');
    const dropdownLinks = document.querySelectorAll('.dropdown-content a');

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            alert('You clicked: ' + link.textContent);
        });
    });

    dropdownLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            alert('You clicked: ' + link.textContent);
        });
    });
});
