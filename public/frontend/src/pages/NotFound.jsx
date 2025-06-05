export default function NotFound() {
  return (
    <div className="not-found">
      <h1>404</h1>
      <h2>Halaman Tidak Ditemukan</h2>
      <p>
        Maaf, halaman yang Anda cari tidak dapat ditemukan.
      </p>
      <a href="/frontend" className="back-link">
        Kembali ke Dashboard
      </a>
    </div>
  );
}
