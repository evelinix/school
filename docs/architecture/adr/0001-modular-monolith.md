# ADR-0001: Modular Monolith

## Status
Accepted

## Konteks
Aplikasi memiliki banyak domain (akademik, siswa, raport, dll) dengan
tim kecil. Microservices menambah kompleksitas operasional yang tidak
sebanding dengan manfaat.

## Keputusan
Menggunakan **Modular Monolith** dengan `nwidart/laravel-modules`.
Setiap modul memiliki boundary jelas (Domain, Application, Infrastructure,
Http) dan siap di-extract menjadi Composer package.

## Konsekuensi
- Deployment tunggal (lebih mudah)
- Boundary disiplin wajib (architecture test)
- Siap migrasi ke microservices jika diperlukan