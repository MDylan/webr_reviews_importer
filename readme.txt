=== Reviews Importer ===
Contributors: Molnár Dávid
Tags: reviews, google, facebook, importer, acf
Requires at least: 6.0
Tested up to: 6.6
Stable tag: 0.5
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Google és Facebook értékelések importálása egyedi post type-ba.  
Headless megoldás arra, hogy letöltse a Google és Facebook értékeléseket és betegye a WordPress adatbázisba.  
A megjelenítésről neked kell gondoskodnod (pl. Oxygen, Bricks stb.).

== Description ==

**Magyar leírás**

Ez a bővítmény headless megoldásként működik: letölti a Google és Facebook értékeléseket, majd eltárolja őket egy egyedi post type-ban a WordPress adatbázisában.  
A megjelenítésről neked kell gondoskodnod (pl. Oxygen Builder, Bricks, Elementor vagy saját sablon).  

**Fő funkciók:**
- Google és Facebook értékelések importálása  
- Értékelések tárolása egyedi post type-ként  
- Angol és magyar nyelvű leírás importálása  
- Óránként futó cron, ami frissíti az értékeléseket  
- ACF mezőkkel kompatibilis (API kulcsok, Page/Place ID-k)  

**Fontos:**  
- Telepítés után importáld be az ACF megfelelő mezőit a json fájlból.  
- Facebook értékelések importálása jelenleg még nincs tesztelve.  

---

**English description**

This plugin provides a headless solution for importing Google and Facebook reviews into a custom post type in the WordPress database.  
You are responsible for displaying the reviews (for example with Oxygen Builder, Bricks, Elementor, or your own theme).  

**Main features:**
- Import Google and Facebook reviews  
- Store reviews as custom post types  
- Supports English and Hungarian content import  
- Hourly cron job keeps reviews updated  
- Compatible with ACF fields (API keys, Page/Place IDs)  

**Note:**  
- After installation, import the corresponding ACF fields.  
- Facebook reviews import has not yet been fully tested.  

== Installation ==

1. Töltsd fel a bővítményt a `/wp-content/plugins/reviews-importer` mappába, vagy telepítsd a WordPress adminon keresztül.  
2. Aktiváld a bővítményt a *Plugins* menüpontban.  
3. Importáld be az ACF mezőcsoportokat (Facebook és Google API kulcsok, Page/Place ID).  
4. A bővítmény óránként automatikusan letölti az új értékeléseket.  

== Frequently Asked Questions ==

= Hogyan jelenítem meg az értékeléseket? =  
A bővítmény nem biztosít frontend megjelenítést. Használhatsz Oxygen, Bricks, Elementor vagy saját template megoldást.

= Tesztelve van a Facebook értékelés importálás? =  
Még nem teljesen, ezért érdemes kezdetben a Google értékelésekre koncentrálni.

== Changelog ==

= 0.5 =
* Első publikus verzió
* Google és Facebook értékelések importálása
* Cron alapú frissítés
* Magyar és angol nyelv támogatás

== Upgrade Notice ==

= 0.5 =
Első verzió – alap funkciók: Google és Facebook értékelések importálása és tárolása.
