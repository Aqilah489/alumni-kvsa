<?php
// footer_kp.php
?>
    <div class="footer">
        <i class="bi bi-c-circle"></i> <?= date('Y') ?> Kolej Vokasional Shah Alam. Hak Cipta Terpelihara.
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('active');
    }
    
    const header = document.querySelector('.simple-header');
    if (header && window.innerWidth <= 768) {
        const menuBtn = document.createElement('button');
        menuBtn.innerHTML = '<i class="bi bi-list"></i>';
        menuBtn.className = 'menu-toggle-btn';
        menuBtn.onclick = toggleSidebar;
        header.insertBefore(menuBtn, header.firstChild);
    }
    
    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.sidebar');
        if (window.innerWidth <= 768 && sidebar && !sidebar.contains(event.target) && !event.target.closest('.menu-toggle-btn')) {
            sidebar.classList.remove('active');
        }
    });
    
    window.addEventListener('resize', function() {
        const sidebar = document.querySelector('.sidebar');
        if (window.innerWidth > 768 && sidebar) {
            sidebar.classList.remove('active');
        }
    });    
    
    function confirmDelete(id, name, module) {
        Swal.fire({
            title: 'Padam ' + name + '?',
            text: "Tindakan ini tidak boleh dibatalkan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'Batal',
            confirmButtonText: 'Ya, Padam!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../../kaunseling/padam.php?id=' + id + '&module=' + module;
            }
        });
    }
</script>
</body>
</html>