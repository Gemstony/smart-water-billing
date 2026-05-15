// Toggle sidebar on button click
document.addEventListener('DOMContentLoaded', function() {
    const sidebarCollapse = document.getElementById('sidebarCollapse');
    if (sidebarCollapse) {
        sidebarCollapse.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
    }

    // Active menu highlighting based on current URL
    const currentLocation = window.location.pathname;
    const menuLinks = document.querySelectorAll('#sidebar ul li a');
    menuLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentLocation.includes(href) && href !== 'index.php') {
            link.closest('li').classList.add('active');
        }
    });

    // Optional: close sidebar on mobile after clicking a link
    const sidebar = document.getElementById('sidebar');
    if (window.innerWidth <= 768) {
        sidebar.classList.add('active');
    }
});