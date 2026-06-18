# Movie Watchlist API

REST API koji prijavljenim korisnicima omogućava da vode svoju listu filmova za gledanje. Kada korisnik doda film, API povuče dodatne podatke o tom filmu sa TMDB-a (The Movie Database) i sačuva relevantne podatke lokalno.

Rađeno u Laravel-u 13, baza je MySQL, a autentikacija ide preko Laravel Sanctum-a (token).

## Šta je implementirano

- Autentikacija: registracija, login i logout. Sve rute za watchlistu su zaštićene i vezane za prijavljenog korisnika.
- Watchlista: dodavanje filma preko IMDb ID-a ili naslova, listanje (filteri, pretraga, sortiranje, paginacija), pregled jednog filma, izmena i brisanje.
- Povlačenje podataka: kada se film doda, podaci se povuku sa TMDB-a i sačuvaju u zajedničku tabelu `movies`, tako da se isti film ne povlači dva puta.
- Konzistentan API: predvidljive rute, ispravni status kodovi, isti format za sve greške i gotovi URL-ovi za postere.
- Validacija i sigurnost: validacija ulaza, provera vlasništva (policy), rate limiting i uredno rukovanje slučajevima kada film ne postoji ili je TMDB nedostupan.
- Testovi: fokusirani testovi koji pokrivaju autentikaciju, rad sa watchlistom, keširanje i mapiranje podataka.

## Pokretanje

Potrebno: PHP 8.2+, Composer i MySQL (npr. iz XAMPP-a).

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Napravi praznu bazu `movie_watchlist` (kroz phpMyAdmin ili `CREATE DATABASE movie_watchlist;`). Podrazumevani `.env` već cilja XAMPP-ov MySQL (host 127.0.0.1, korisnik root, bez lozinke).

Ubaci svoj TMDB token u `.env` (`TMDB_TOKEN=...`), pa:

```bash
php artisan migrate
php artisan serve
```

API je dostupan na `http://127.0.0.1:8000/api`.

## TMDB API ključ

1. Napravi besplatan nalog na https://www.themoviedb.org/
2. Idi na Settings → API i zatraži ključ (opcija Developer, kratak formular, odobrenje je odmah).
3. Kopiraj "API Read Access Token" (dugi token, ne kratki "API Key") i stavi ga u `.env` kao `TMDB_TOKEN`.

Token se šalje kao Bearer, pa ne završava u URL-u.

## Testiranje endpoint-a (Postman / .http)

U `docs/` folderu su dva načina:

- Postman kolekcija (`docs/movie-watchlist.postman_collection.json`). Pokreni prvo Register ili Login, token se automatski sačuva, pa ostale rute rade odmah.
- `.http` fajl (`docs/api.http`) za PhpStorm ili VS Code (REST Client). Radi na isti način.

## Autentikacija

Koristio sam Laravel Sanctum u režimu API tokena. Pošto je ovo API koji se poziva spolja (Postman, eventualno mobilna aplikacija), token je prirodno rešenje: na registraciji ili login-u korisnik dobije token i šalje ga kao `Authorization: Bearer <token>`, a zaštićene rute koriste `auth:sanctum`. Logout poništava samo token sa kojim je zahtev poslat.

Passport (pun OAuth2) bi ovde bio previše, a JWT paket nije first-party i ne donosi ništa što Sanctum već ne pokriva.

## Zašto TMDB

Od dva predložena API-ja (OMDb i TMDB) izabrao sam TMDB jer ima bogatije podatke (žanrovi, trajanje, ocene, posteri) i podržava i pretragu po IMDb ID-u i po naslovu.

## Organizacija koda

Ideja je da svaki sloj radi jednu stvar i da poslovna logika ne zna za HTTP:

- `Controllers`: samo prime validiran zahtev, pozovu servis i vrate Resource.
- `Requests` (Form Request): validacija ulaza pre nego što kontroler uopšte krene.
- `Resources`: oblikuju JSON odgovor, da svuda izgleda isto.
- `Services`: poslovna logika; primaju obične vrednosti, vraćaju modele ili bacaju izuzetke.
- `Services/Movies`: integracija sa TMDB (interfejs i `TmdbClient` koji mapira TMDB JSON u DTO).
- `DataTransferObjects`: `MovieData`, normalizovani podaci nezavisni od provajdera.
- `Policies`: provera da korisnik dira samo svoje stavke.
- `Enums`: `WatchlistStatus` i `Priority`.

### Service sloj

Kontroler poziva servis, a servis radi posao. Par stvari na koje sam pazio:

- servisi se ubrizgavaju kroz konstruktor (dependency injection), ne pravim ih sa `new`,
- servisi ne vraćaju `JsonResponse` i ne primaju `Request`; to je posao kontrolera, Form Request-a i Resource-a,
- TMDB je iza interfejsa (`MovieDataProvider`), pa bi zamena provajdera bila jednostavna.

## Model podataka

Dve glavne tabele, namerno razdvojene:

- `movies`: podaci o filmu sa TMDB-a, zajednički za sve korisnike. Film se povuče jednom i svi ga koriste. Tu su `tmdb_id`, `imdb_id`, polja o filmu, ceo originalni odgovor (`raw_payload`) i `cached_at` za osvežavanje.
- `watchlist_items`: veza korisnika i filma, sa poljima specifičnim za korisnika: `status`, `priority`, lična ocena (`rating`), beleške (`notes`) i `watched_at`. Jedinstveni par (`user_id`, `movie_id`) sprečava da isti film bude dodat dvaput.

Razdvajanje "filma" od "korisnikovog odnosa prema filmu" drži broj poziva ka TMDB-u na minimumu i bazu čistom.

## API rute

Sve su pod `/api`:

- `POST /api/register`: registracija, vraća token
- `POST /api/login`: login, vraća token
- `POST /api/logout`: poništava trenutni token
- `GET /api/user`: trenutno prijavljeni korisnik
- `GET /api/watchlist`: lista (filteri: status, priority, search, sort, per_page)
- `POST /api/watchlist`: dodavanje filma (imdb_id ili title, plus opciono status, priority, rating, notes)
- `GET /api/watchlist/{id}`: jedan film
- `PATCH /api/watchlist/{id}`: izmena (status, priority, rating, notes)
- `DELETE /api/watchlist/{id}`: brisanje

Primere zahteva i odgovora ne navodim ovde jer postoje u Postman kolekciji u `docs/`.

## Integracija sa TMDB

- Kada se film dodaje, prvo se proverava lokalna `movies` tabela; TMDB se zove samo ako film ne postoji ili je zastareo. Tako se isti film povlači jednom za sve korisnike.
- `cached_at` i TTL: posle nekog vremena se podaci osveže (podrazumevano 7 dana).
- `TmdbClient` mapira TMDB-ov JSON u `MovieData` DTO, pa ostatak koda ne zavisi od TMDB-ovih naziva polja.
- Ako film ne postoji, vraća se 404; ako je TMDB nedostupan ili token nije podešen, vraća se 503 sa jasnom porukom.

## Testovi

```bash
php artisan test
```

Testovi koriste SQLite u memoriji, a TMDB je mock-ovan (`Http::fake`), tako da ne treba ni MySQL ni internet. Pokrivaju autentikaciju, rad sa watchlistom (uključujući da korisnik ne može da vidi tuđe stavke), keširanje (TMDB se zove samo jednom za isti film) i mapiranje podataka.

## Odluke i kompromisi

Trudio sam se da arhitektura bude čista i po slojevima, tako da bi kolega lako mogao da je nadogradi.

Šta sam svesno preskočio:

- Asinhrono povlačenje preko queue-a: sinhrono uz keš je za ovaj obim sasvim dovoljno i bolje za korisnika.
- TV serije: TMDB ih podržava, ali zadatak traži filmove.
- Istek i refresh tokena: za ovakvu aplikaciju nije potrebno, logout koji poništava token je dovoljan.

## Pretpostavke

- MySQL preko XAMPP-a (127.0.0.1, root, bez lozinke, baza `movie_watchlist`). Za drugačiji setup samo izmeniti `.env`.
- Kod dodavanja po naslovu uzima se najpopularniji rezultat sa TMDB-a, a opcioni `year` pomaže kod istih naslova.
- Isti film se ne može dodati dvaput; drugi pokušaj vraća 409.
- Brisanje je "soft delete" (može da se povrati u bazi), iako ga API prikazuje kao obično uklanjanje.
