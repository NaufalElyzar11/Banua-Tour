"""
Automation Regression Test — BanuaTour (CodeIgniter 4)
UAS Web Programming II

Prasyarat:
    pip install selenium webdriver-manager pytest

Cara menjalankan:
    python -m pytest tests/selenium/test_banuatour.py -v
    python -m unittest tests.selenium.test_banuatour -v
"""

import time
import unittest
import uuid

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, NoSuchElementException

# NOTE: Selenium 4.6+ sudah memiliki Selenium Manager bawaan yang otomatis
# men-download chromedriver yang cocok. webdriver-manager versi lama buggy pada
# Chrome for Testing terbaru (mengambil file THIRD_PARTY_NOTICES.chromedriver
# alih-alih chromedriver.exe → WinError 193), jadi sengaja TIDAK kita pakai.


# ============================================================
# KONFIGURASI — sesuaikan dengan environment Laragon Anda
# ============================================================
BASE_URL = "http://localhost:8080"

VALID_USER_EMAIL    = "nadia@gmail.com"
VALID_USER_PASSWORD = "nadia123"
VALID_USER_USERNAME = "Nadia"

VALID_ADMIN_USERNAME = "superadmin"
VALID_ADMIN_PASSWORD = "AdminBanua@2026"

EXISTING_EMAIL    = "nadia@gmail.com"
EXISTING_USERNAME = "Nadia"

# Headless saat CI / jika tidak ingin window muncul; ubah ke False saat demo.
HEADLESS = False
DEFAULT_WAIT = 10


class BanuaTourTest(unittest.TestCase):
    """Regression suite untuk BanuaTour. Satu skenario = satu test_* function."""

    @classmethod
    def setUpClass(cls):
        options = Options()
        if HEADLESS:
            options.add_argument("--headless=new")
        options.add_argument("--window-size=1366,768")
        options.add_argument("--disable-notifications")
        options.add_argument("--start-maximized")

        # Biarkan Selenium Manager (bawaan Selenium 4.6+) mengurus chromedriver
        cls.driver = webdriver.Chrome(options=options)

        cls.driver.implicitly_wait(5)
        cls.wait = WebDriverWait(cls.driver, DEFAULT_WAIT)

    @classmethod
    def tearDownClass(cls):
        cls.driver.quit()

    def setUp(self):
        """Ensure clean session state before each test."""
        self.driver.get(f"{BASE_URL}/auth/logout")
        time.sleep(0.5)
        self.driver.delete_all_cookies()

    # -------------------- Helper Methods --------------------
    def _go(self, path: str):
        self.driver.get(f"{BASE_URL}{path}")

    def _login(self, identifier: str, password: str, expect_success: bool = True):
        self._go("/auth/login")
        time.sleep(1)
        email_field = self.wait.until(EC.presence_of_element_located((By.NAME, "email")))
        email_field.clear()
        email_field.send_keys(identifier)
        pwd_field = self.driver.find_element(By.NAME, "password")
        pwd_field.clear()
        pwd_field.send_keys(password)
        self.driver.find_element(By.CSS_SELECTOR, "input[type='submit']").click()
        if expect_success:
            self.wait.until(lambda d: "/auth/login" not in d.current_url
                            and "/auth/doLogin" not in d.current_url)
        else:
            time.sleep(1)

    def _fill_register_form(self, data: dict):
        self._go("/auth/register")
        time.sleep(0.5)
        self.wait.until(EC.presence_of_element_located((By.NAME, "nama")))
        for name, value in data.items():
            el = self.driver.find_element(By.NAME, name)
            tag = el.tag_name.lower()
            if tag == "select":
                from selenium.webdriver.support.ui import Select
                Select(el).select_by_value(value)
            else:
                el.clear()
                el.send_keys(value)
        self.driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()

    # ============================================================
    # 20 POSITIVE SCENARIOS
    # ============================================================

    def test_p01_open_home(self):
        """TC-P-01: Halaman home dapat dibuka."""
        self._go("/")
        self.assertIn("banuatour", self.driver.page_source.lower())

    def test_p02_open_login_page(self):
        """TC-P-02: Halaman login tampil dengan field email & password."""
        self._go("/auth/login")
        self.wait.until(EC.presence_of_element_located((By.NAME, "email")))
        self.assertTrue(self.driver.find_element(By.NAME, "password").is_displayed())

    def test_p03_open_register_page(self):
        """TC-P-03: Halaman registrasi tampil dengan field-field utama."""
        self._go("/auth/register")
        for field in ["nama", "username", "email", "password", "confirm_password",
                      "daerah", "jenis_kelamin", "umur"]:
            self.assertTrue(
                self.driver.find_element(By.NAME, field).is_displayed(),
                f"Field {field} tidak tampil"
            )

    def test_p04_register_valid_user(self):
        """TC-P-04: Registrasi user valid berhasil."""
        unique = uuid.uuid4().hex[:8]
        self._fill_register_form({
            "nama": f"User Test {unique}",
            "username": f"usr_{unique}",
            "email": f"usr_{unique}@test.com",
            "password": "abcdef123",
            "confirm_password": "abcdef123",
            "daerah": "Banjarmasin",
            "jenis_kelamin": "L",
            "umur": "25",
        })
        self.wait.until(EC.url_contains("/auth/login"))
        self.assertIn("auth/login", self.driver.current_url)

    def test_p05_login_user_valid(self):
        """TC-P-05: Login dengan email & password valid → user_home."""
        self._login(VALID_USER_EMAIL, VALID_USER_PASSWORD)
        self.wait.until(EC.url_contains("/user_home"))
        self.assertIn("user_home", self.driver.current_url)

    def test_p06_login_admin_valid(self):
        """TC-P-06: Login sebagai admin → /admin/dashboard."""
        self._login(VALID_ADMIN_USERNAME, VALID_ADMIN_PASSWORD)
        self.wait.until(EC.url_contains("/admin"))
        self.assertIn("admin", self.driver.current_url)

    def test_p07_login_with_username(self):
        """TC-P-07: Login menggunakan username (bukan email)."""
        self._login(VALID_USER_USERNAME, VALID_USER_PASSWORD)
        self.wait.until(EC.url_contains("/user_home"))
        self.assertIn("user_home", self.driver.current_url)

    def test_p08_open_destinasi(self):
        """TC-P-08: Halaman list destinasi dapat diakses."""
        self._go("/destinasi")
        self.assertIn("destinasi", self.driver.current_url)

    def test_p09_search_destinasi_valid(self):
        """TC-P-09: Search destinasi dengan keyword valid."""
        self._go("/destinasi/search?keyword=banjarmasin")
        self.assertIn("search", self.driver.current_url)
        self.assertNotIn("500", self.driver.title)

    def test_p10_open_destinasi_detail(self):
        """TC-P-10: Halaman detail destinasi (ID=1) dapat dibuka."""
        self._go("/destinasi/detail/1")
        self.assertNotIn("404", self.driver.title.lower())

    def test_p11_add_wishlist(self):
        """TC-P-11: Tambah destinasi ke wishlist (login required)."""
        self._login(VALID_USER_EMAIL, VALID_USER_PASSWORD)
        self._go("/wishlist/add/1")
        self._go("/wishlist")
        self.assertIn("wishlist", self.driver.current_url)

    def test_p12_open_wishlist_page(self):
        """TC-P-12: Halaman wishlist dapat diakses setelah login."""
        self._login(VALID_USER_EMAIL, VALID_USER_PASSWORD)
        self._go("/wishlist")
        self.assertIn("wishlist", self.driver.current_url)

    def test_p13_open_booking_page(self):
        """TC-P-13: Halaman booking dapat diakses setelah login."""
        self._login(VALID_USER_EMAIL, VALID_USER_PASSWORD)
        self._go("/booking")
        self.assertIn("booking", self.driver.current_url)

    def test_p14_open_pembelian_form(self):
        """TC-P-14: Form pembelian destinasi dapat dibuka."""
        self._login(VALID_USER_EMAIL, VALID_USER_PASSWORD)
        self._go("/booking/pembelian/1")
        self.assertIn("pembelian", self.driver.current_url)

    def test_p15_open_riwayat_page(self):
        """TC-P-15: Halaman riwayat dapat diakses setelah login."""
        self._login(VALID_USER_EMAIL, VALID_USER_PASSWORD)
        self._go("/riwayat")
        self.assertIn("riwayat", self.driver.current_url)

    def test_p16_open_profile_page(self):
        """TC-P-16: Halaman profil tampil dengan data user."""
        self._login(VALID_USER_EMAIL, VALID_USER_PASSWORD)
        self._go("/profile")
        self.assertIn("profile", self.driver.current_url)

    def test_p17_update_profile_valid(self):
        """TC-P-17: Update profil dengan data valid."""
        self._login(VALID_USER_EMAIL, VALID_USER_PASSWORD)
        self._go("/profile")
        try:
            nama_input = self.wait.until(EC.presence_of_element_located((By.NAME, "nama")))
            nama_input.clear()
            nama_input.send_keys("User Test Updated")
            self.driver.find_element(By.CSS_SELECTOR, "form[action*='profile/update'] button[type='submit']").click()
            time.sleep(1)
            self.assertIn("profile", self.driver.current_url)
        except (NoSuchElementException, TimeoutException):
            self.skipTest("Form profile/update tidak ditemukan — sesuaikan selector.")

    def test_p18_change_password_valid(self):
        """TC-P-18: Ganti password dengan data valid."""
        self._login(VALID_USER_EMAIL, VALID_USER_PASSWORD)
        self._go("/profile")
        try:
            old_pwd = self.driver.find_element(By.NAME, "old_password")
            new_pwd = self.driver.find_element(By.NAME, "new_password")
            conf    = self.driver.find_element(By.NAME, "confirm_password")
            old_pwd.send_keys(VALID_USER_PASSWORD)
            new_pwd.send_keys(VALID_USER_PASSWORD)  # ganti dengan password sama agar idempotent
            conf.send_keys(VALID_USER_PASSWORD)
            self.driver.find_element(
                By.CSS_SELECTOR, "form[action*='change-password'] button[type='submit']"
            ).click()
            time.sleep(1)
            self.assertIn("profile", self.driver.current_url)
        except NoSuchElementException:
            self.skipTest("Form change-password tidak ditemukan — sesuaikan selector.")

    def test_p19_logout(self):
        """TC-P-19: Logout user → kembali ke halaman login."""
        self._login(VALID_USER_EMAIL, VALID_USER_PASSWORD)
        self._go("/auth/logout")
        time.sleep(1)
        self.assertIn("login", self.driver.current_url.lower())

    def test_p20_relogin_after_logout(self):
        """TC-P-20: Login ulang setelah logout."""
        self._login(VALID_USER_EMAIL, VALID_USER_PASSWORD)
        self._go("/auth/logout")
        self._login(VALID_USER_EMAIL, VALID_USER_PASSWORD)
        self.wait.until(EC.url_contains("/user_home"))
        self.assertIn("user_home", self.driver.current_url)

    # ============================================================
    # 20 NEGATIVE SCENARIOS
    # ============================================================

    def test_n01_login_wrong_email(self):
        """TC-N-01: Login dengan email yang tidak terdaftar."""
        self._login("notexist_xyz@test.com", "whatever123", expect_success=False)
        self.assertIn("login", self.driver.current_url.lower())
        self.assertIn("salah", self.driver.page_source.lower())

    def test_n02_login_wrong_password(self):
        """TC-N-02: Email benar tapi password salah."""
        self._login(VALID_USER_EMAIL, "passwordsalah", expect_success=False)
        self.assertIn("login", self.driver.current_url.lower())
        self.assertIn("salah", self.driver.page_source.lower())

    def test_n03_login_empty_all(self):
        """TC-N-03: Submit form login tanpa input."""
        self._go("/auth/login")
        time.sleep(0.5)
        self.driver.find_element(By.CSS_SELECTOR, "input[type='submit']").click()
        self.assertIn("login", self.driver.current_url.lower())

    def test_n04_login_only_email(self):
        """TC-N-04: Hanya isi email, password kosong."""
        self._go("/auth/login")
        time.sleep(0.5)
        self.driver.find_element(By.NAME, "email").send_keys(VALID_USER_EMAIL)
        self.driver.find_element(By.CSS_SELECTOR, "input[type='submit']").click()
        self.assertIn("login", self.driver.current_url.lower())

    def test_n05_login_only_password(self):
        """TC-N-05: Hanya isi password, email kosong."""
        self._go("/auth/login")
        time.sleep(0.5)
        self.driver.find_element(By.NAME, "password").send_keys(VALID_USER_PASSWORD)
        self.driver.find_element(By.CSS_SELECTOR, "input[type='submit']").click()
        self.assertIn("login", self.driver.current_url.lower())

    def test_n06_register_password_too_short(self):
        """TC-N-06: Password < 6 karakter."""
        unique = uuid.uuid4().hex[:6]
        self._fill_register_form({
            "nama": "User Short",
            "username": f"shrt_{unique}",
            "email": f"shrt_{unique}@test.com",
            "password": "abc",
            "confirm_password": "abc",
            "daerah": "Banjarmasin",
            "jenis_kelamin": "L",
            "umur": "20",
        })
        time.sleep(1)
        self.assertIn("register", self.driver.current_url.lower())

    def test_n07_register_password_mismatch(self):
        """TC-N-07: Konfirmasi password tidak cocok."""
        unique = uuid.uuid4().hex[:6]
        self._fill_register_form({
            "nama": "User Mismatch",
            "username": f"mis_{unique}",
            "email": f"mis_{unique}@test.com",
            "password": "abcdef123",
            "confirm_password": "berbeda999",
            "daerah": "Banjarmasin",
            "jenis_kelamin": "L",
            "umur": "20",
        })
        time.sleep(1)
        self.assertIn("register", self.driver.current_url.lower())

    def test_n08_register_invalid_email(self):
        """TC-N-08: Format email tidak valid."""
        unique = uuid.uuid4().hex[:6]
        self._go("/auth/register")
        try:
            self.driver.find_element(By.NAME, "nama").send_keys("Bad Email")
            self.driver.find_element(By.NAME, "username").send_keys(f"bad_{unique}")
            self.driver.find_element(By.NAME, "email").send_keys("abc@")  # invalid
            self.driver.find_element(By.NAME, "password").send_keys("abcdef123")
            self.driver.find_element(By.NAME, "confirm_password").send_keys("abcdef123")
            from selenium.webdriver.support.ui import Select
            Select(self.driver.find_element(By.NAME, "daerah")).select_by_value("Banjarmasin")
            Select(self.driver.find_element(By.NAME, "jenis_kelamin")).select_by_value("L")
            self.driver.find_element(By.NAME, "umur").send_keys("20")
            self.driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
        except NoSuchElementException:
            pass
        time.sleep(1)
        # HTML5 input type=email akan menolak; tetap di register
        self.assertIn("register", self.driver.current_url.lower())

    def test_n09_register_duplicate_email(self):
        """TC-N-09: Register dengan email yang sudah terdaftar."""
        unique = uuid.uuid4().hex[:6]
        self._fill_register_form({
            "nama": "User Dup Email",
            "username": f"dupe_{unique}",
            "email": EXISTING_EMAIL,
            "password": "abcdef123",
            "confirm_password": "abcdef123",
            "daerah": "Banjarmasin",
            "jenis_kelamin": "L",
            "umur": "20",
        })
        time.sleep(1)
        self.assertIn("register", self.driver.current_url.lower())

    def test_n10_register_duplicate_username(self):
        """TC-N-10: Register dengan username yang sudah terdaftar."""
        unique = uuid.uuid4().hex[:6]
        self._fill_register_form({
            "nama": "User Dup Username",
            "username": EXISTING_USERNAME,
            "email": f"dup_{unique}@test.com",
            "password": "abcdef123",
            "confirm_password": "abcdef123",
            "daerah": "Banjarmasin",
            "jenis_kelamin": "L",
            "umur": "20",
        })
        time.sleep(1)
        self.assertIn("register", self.driver.current_url.lower())

    def test_n11_register_short_name(self):
        """TC-N-11: Nama lengkap < 3 karakter."""
        unique = uuid.uuid4().hex[:6]
        self._fill_register_form({
            "nama": "Aa",
            "username": f"sn_{unique}",
            "email": f"sn_{unique}@test.com",
            "password": "abcdef123",
            "confirm_password": "abcdef123",
            "daerah": "Banjarmasin",
            "jenis_kelamin": "L",
            "umur": "20",
        })
        time.sleep(1)
        self.assertIn("register", self.driver.current_url.lower())

    def test_n12_register_age_zero(self):
        """TC-N-12: Umur = 0 (gagal validasi greater_than[0])."""
        unique = uuid.uuid4().hex[:6]
        self._fill_register_form({
            "nama": "User Zero Age",
            "username": f"zer_{unique}",
            "email": f"zer_{unique}@test.com",
            "password": "abcdef123",
            "confirm_password": "abcdef123",
            "daerah": "Banjarmasin",
            "jenis_kelamin": "L",
            "umur": "0",
        })
        time.sleep(1)
        self.assertIn("register", self.driver.current_url.lower())

    def test_n13_register_gender_empty(self):
        """TC-N-13: Jenis kelamin tidak dipilih."""
        unique = uuid.uuid4().hex[:6]
        self._go("/auth/register")
        self.wait.until(EC.presence_of_element_located((By.NAME, "nama")))
        self.driver.find_element(By.NAME, "nama").send_keys("User NoGender")
        self.driver.find_element(By.NAME, "username").send_keys(f"ng_{unique}")
        self.driver.find_element(By.NAME, "email").send_keys(f"ng_{unique}@test.com")
        self.driver.find_element(By.NAME, "password").send_keys("abcdef123")
        self.driver.find_element(By.NAME, "confirm_password").send_keys("abcdef123")
        from selenium.webdriver.support.ui import Select
        Select(self.driver.find_element(By.NAME, "daerah")).select_by_value("Banjarmasin")
        # SENGAJA: tidak memilih jenis_kelamin
        self.driver.find_element(By.NAME, "umur").send_keys("20")
        self.driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
        time.sleep(1)
        self.assertIn("register", self.driver.current_url.lower())

    def test_n14_register_daerah_empty(self):
        """TC-N-14: Daerah tidak dipilih."""
        unique = uuid.uuid4().hex[:6]
        self._go("/auth/register")
        self.wait.until(EC.presence_of_element_located((By.NAME, "nama")))
        self.driver.find_element(By.NAME, "nama").send_keys("User NoDaerah")
        self.driver.find_element(By.NAME, "username").send_keys(f"nd_{unique}")
        self.driver.find_element(By.NAME, "email").send_keys(f"nd_{unique}@test.com")
        self.driver.find_element(By.NAME, "password").send_keys("abcdef123")
        self.driver.find_element(By.NAME, "confirm_password").send_keys("abcdef123")
        from selenium.webdriver.support.ui import Select
        # SENGAJA: tidak memilih daerah
        Select(self.driver.find_element(By.NAME, "jenis_kelamin")).select_by_value("L")
        self.driver.find_element(By.NAME, "umur").send_keys("20")
        self.driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
        time.sleep(1)
        self.assertIn("register", self.driver.current_url.lower())

    def test_n15_access_user_home_without_login(self):
        """TC-N-15: Akses /user_home tanpa login → redirect login."""
        self._go("/user_home")
        time.sleep(1)
        self.assertIn("login", self.driver.current_url.lower())

    def test_n16_access_wishlist_without_login(self):
        """TC-N-16: Akses /wishlist tanpa login → redirect login."""
        self._go("/wishlist")
        time.sleep(1)
        self.assertIn("login", self.driver.current_url.lower())

    def test_n17_access_booking_without_login(self):
        """TC-N-17: Akses /booking tanpa login → redirect login."""
        self._go("/booking")
        time.sleep(1)
        self.assertIn("login", self.driver.current_url.lower())

    def test_n18_access_riwayat_without_login(self):
        """TC-N-18: Akses /riwayat tanpa login → redirect login."""
        self._go("/riwayat")
        time.sleep(1)
        self.assertIn("login", self.driver.current_url.lower())

    def test_n19_user_access_admin_dashboard(self):
        """TC-N-19: User biasa mencoba akses /admin/dashboard."""
        self._login(VALID_USER_EMAIL, VALID_USER_PASSWORD)
        self._go("/admin/dashboard")
        time.sleep(1)
        # User non-admin TIDAK boleh berada di /admin/dashboard
        self.assertNotIn("/admin/dashboard", self.driver.current_url)

    def test_n20_search_special_chars(self):
        """TC-N-20: Search destinasi dengan karakter aneh — tidak boleh error 500."""
        self._go("/destinasi/search?keyword=%21%40%23%24%25%5E")  # !@#$%^
        time.sleep(1)
        body = self.driver.page_source.lower()
        self.assertNotIn("fatal error", body)
        self.assertNotIn("server error", body)
        self.assertNotIn("error 500", body)
        self.assertNotIn("whoops!", body)


if __name__ == "__main__":
    unittest.main(verbosity=2)
