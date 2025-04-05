<?php
if (!function_exists('charIndex')) {
	function charIndex(string $huruf)
	{
		// Konversi huruf menjadi huruf kapital untuk memudahkan pemrosesan
		$huruf = strtoupper($huruf);

		// Validasi bahwa karakter input adalah huruf
		if (ctype_alpha($huruf)) {
			// Hitung panjang string input
			$panjang = strlen($huruf);

			// Inisialisasi variabel untuk menyimpan hasil konversi
			$angka = 0;

			// Iterasi dari karakter paling kiri ke kanan
			for ($i = 0; $i < $panjang; $i++) {
				// Konversi huruf ke angka berdasarkan posisi dalam alfabet
				$nilaiHuruf = ord($huruf[$i]) - ord('A');

				// Perbarui nilai dengan mengalikan dengan 26 pangkat (panjang-1-i)
				$angka += $nilaiHuruf * pow(26, $panjang - 1 - $i);
			}

			return $angka;
		} else {
			// Karakter input bukan huruf
			return 0;
		}
	}
}
