<?php
// footer.php - Footer untuk kaunseling module
?>
    <!-- Footer -->
    <div class="footer">
        <i class="bi bi-c-circle"></i> <?= date('Y') ?> Kolej Vokasional Shah Alam. Hak Cipta Terpelihara.
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Toggle sidebar on mobile
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('active');
    }
    
    // Add menu button for mobile
    const header = document.querySelector('.simple-header');
    if (header && window.innerWidth <= 768) {
        const menuBtn = document.createElement('button');
        menuBtn.innerHTML = '<i class="bi bi-list"></i>';
        menuBtn.className = 'menu-toggle-btn';
        menuBtn.style.cssText = 'background:none;border:none;font-size:1.5rem;color:var(--primary);cursor:pointer;margin-right:10px;';
        menuBtn.onclick = toggleSidebar;
        header.insertBefore(menuBtn, header.firstChild);
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.sidebar');
        const isMobile = window.innerWidth <= 768;
        if (isMobile && sidebar && !sidebar.contains(event.target) && !event.target.closest('.menu-toggle-btn')) {
            sidebar.classList.remove('active');
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        const sidebar = document.querySelector('.sidebar');
        if (window.innerWidth > 768 && sidebar) {
            sidebar.classList.remove('active');
        }
    });

    // Confirm delete function
    function confirmDelete(id, name, module) {
    Swal.fire({
        title: 'Padam ' + name + '?',
        text: "Tindakan ini tidak boleh dibatalkan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Padam!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Panggil padam.php kat root kaunseling
            window.location.href = '../padam.php?id=' + id + '&module=' + module;
        }
    });
}
</script>

<?php
// Display SweetAlert for session messages
if(isset($_SESSION['success'])): ?>
<script>
Swal.fire({
    title: 'Berjaya!',
    text: '<?= addslashes($_SESSION['success']) ?>',
    icon: 'success',
    confirmButtonColor: '#28a745',
    timer: 3000,
    showConfirmButton: true
});
</script>
<?php unset($_SESSION['success']); endif; ?>

<?php if(isset($_SESSION['error'])): ?>
<script>
Swal.fire({
    title: 'Gagal!',
    text: '<?= addslashes($_SESSION['error']) ?>',
    icon: 'error',
    confirmButtonColor: '#dc3545'
});
</script>
<?php unset($_SESSION['error']); endif; ?>

</body>
</html>