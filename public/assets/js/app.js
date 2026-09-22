document.addEventListener('DOMContentLoaded', function () {
  // 1. AJAX Tambah ke Keranjang
  const addCartBtns = document.querySelectorAll('.btn-add-cart');
  addCartBtns.forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const productId = this.dataset.productId;
      const qty = document.getElementById('product-qty') ? document.getElementById('product-qty').value : 1;
      const originalText = this.innerHTML;

      this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menambahkan...';
      this.disabled = true;

      const formData = new FormData();
      formData.append('product_id', productId);
      formData.append('qty', qty);

      fetch(window.BASE_URL + 'keranjang/add', {
        method: 'POST',
        body: formData,
      })
      .then(res => res.json())
      .then(data => {
        this.innerHTML = originalText;
        this.disabled = false;

        if (data.redirect) {
          window.location.href = data.redirect;
          return;
        }

        if (data.success) {
          // Update badge keranjang
          const badge = document.querySelector('.cart-badge');
          if (badge) {
            badge.innerText = data.cart_count;
            badge.classList.remove('d-none');
          }

          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: data.message,
            showCancelButton: true,
            confirmButtonColor: '#15803d',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Lihat Keranjang',
            cancelButtonText: 'Lanjut Belanja'
          }).then((result) => {
            if (result.isConfirmed) {
              window.location.href = window.BASE_URL + 'keranjang';
            }
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: data.message || 'Terjadi kesalahan saat menambahkan produk.',
            confirmButtonColor: '#15803d'
          });
        }
      })
      .catch(err => {
        this.innerHTML = originalText;
        this.disabled = false;
        console.error(err);
      });
    });
  });

  // 2. AJAX Wishlist Toggle
  const wishlistBtns = document.querySelectorAll('.product-wishlist-btn');
  wishlistBtns.forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const productId = this.dataset.productId;

      const formData = new FormData();
      formData.append('product_id', productId);

      fetch(window.BASE_URL + 'wishlist/toggle', {
        method: 'POST',
        body: formData,
      })
      .then(res => res.json())
      .then(data => {
        if (data.redirect) {
          window.location.href = data.redirect;
          return;
        }

        if (data.success) {
          const icon = this.querySelector('i');
          if (data.status === 'added') {
            this.classList.add('active');
            if (icon) {
              icon.classList.remove('bi-heart');
              icon.classList.add('bi-heart-fill');
            }
          } else {
            this.classList.remove('active');
            if (icon) {
              icon.classList.remove('bi-heart-fill');
              icon.classList.add('bi-heart');
            }
          }

          const toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
          });
          toast.fire({
            icon: 'success',
            title: data.message
          });
        }
      })
      .catch(err => console.error(err));
    });
  });

  // 3. Live Search Autocomplete di Navbar
  const searchInput = document.getElementById('navbar-search-input');
  const searchDropdown = document.getElementById('navbar-search-dropdown');

  if (searchInput && searchDropdown) {
    let debounceTimer;
    searchInput.addEventListener('input', function () {
      clearTimeout(debounceTimer);
      const query = this.value.trim();

      if (query.length < 2) {
        searchDropdown.classList.add('d-none');
        searchDropdown.innerHTML = '';
        return;
      }

      debounceTimer = setTimeout(() => {
        fetch(window.BASE_URL + 'api/search?term=' + encodeURIComponent(query))
          .then(res => res.json())
          .then(items => {
            if (items.length > 0) {
              let html = '<div class="p-2 border-bottom text-muted small fw-semibold">Hasil Pencarian Produk Perkebunan:</div>';
              items.forEach(item => {
                html += `
                  <a href="${item.url}" class="d-flex align-items-center gap-3 p-2 text-decoration-none border-bottom search-item-result text-dark">
                    <img src="${item.foto}" alt="${item.nama}" style="width: 44px; height: 44px; object-fit: cover; border-radius: 8px;">
                    <div>
                      <div class="fw-bold small text-truncate" style="max-width: 260px;">${item.nama}</div>
                      <div class="text-success fw-bold small">${item.harga} <span class="text-muted fw-normal">/ ${item.satuan}</span></div>
                    </div>
                  </a>
                `;
              });
              html += `<div class="p-2 text-center"><a href="${window.BASE_URL}katalog?q=${encodeURIComponent(query)}" class="small fw-bold text-success">Lihat semua hasil →</a></div>`;
              searchDropdown.innerHTML = html;
              searchDropdown.classList.remove('d-none');
            } else {
              searchDropdown.innerHTML = '<div class="p-3 text-center text-muted small">Tidak ada produk perkebunan yang cocok.</div>';
              searchDropdown.classList.remove('d-none');
            }
          });
      }, 300);
    });

    document.addEventListener('click', function (e) {
      if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
        searchDropdown.classList.add('d-none');
      }
    });
  }

  // 4. Konfirmasi Hapus SweetAlert2
  const deleteBtns = document.querySelectorAll('.btn-delete-confirm');
  deleteBtns.forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const targetUrl = this.getAttribute('href');
      Swal.fire({
        title: 'Konfirmasi Penghapusan',
        text: 'Apakah Anda yakin ingin menghapus data ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = targetUrl;
        }
      });
    });
  });
});
