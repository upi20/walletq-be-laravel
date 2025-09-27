sekarang kita ke list data lagi. pelajari dulu 
model: app/Models
types: resources/js/types/index.d.ts
kemudian List data: resources/js/pages/Transactions/Index.vue

kemudian controller: app/Http/Controllers/Web/Transactions/TransactionController.php

dan service: app/Services/Transaction/TransactionService.php

saya ingin membuat filter yang lengkap.

mingguan, bulanan, harian, tahunan, semua,
kategori transaksi, pencarian teks, uang masuk, uang keluar, kostum tanggal. dan lain nya selengkap mungkin. dengan metode sederhana.

Controller tetap 1 file. service tetap 1 file.
tiap filter harus ada di url.

pelajari dulu file yang ada. kemudian apa saja yang bisa ditambahkan atau diubah.

dan tampilan juga harus sesuai dengan desain sistem yang sudah ada design_system.json.

bisa filter di item list nya contoh.

ada account dompet bisa di klik sehingga filter per account dompet resources/js/pages/Transactions/Partials/TransactionCard.vue

dan lain nya sehingga fitur menampilkan data taransaksi ini powerful.