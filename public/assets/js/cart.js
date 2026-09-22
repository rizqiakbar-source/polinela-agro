/**
 * Polinela Agro Digital - Cart Helper Module
 */
const Cart = {
    updateQty: function (cartId, qty, callback) {
        const formData = new FormData();
        formData.append('cart_id', cartId);
        formData.append('qty', qty);

        fetch(window.BASE_URL + 'keranjang/update', {
            method: 'POST',
            body: formData,
        })
        .then(res => res.json())
        .then(data => {
            if (typeof callback === 'function') {
                callback(data);
            }
        })
        .catch(err => console.error('Cart update error:', err));
    },

    deleteItem: function (cartId) {
        Swal.fire({
            title: 'Hapus Produk?',
            text: 'Produk ini akan dikeluarkan dari keranjang belanja Anda.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = window.BASE_URL + 'keranjang/delete/' + cartId;
            }
        });
    }
};
