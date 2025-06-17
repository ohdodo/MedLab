<?php
require_once 'session_check.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MEDLAB</title>
    <link rel="stylesheet" href="design.css" />
    <link
      rel="stylesheet"
      href="https://pro.fontawesome.com/releases/v5.15.0/css/all.css"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Inconsolata:wght@200..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
      rel="stylesheet"
    />
  </head>
  <body>
    <header>
      <input type="checkbox" name="" id="toggler" />
      <label for="toggler" class="fa fa-bars"></label>
      <img id="logo" src="medlab_logo.png" />
      <nav class="navbar">
        <a href="#home">Home</a>
        <a href="#about_us">About Us</a>
        <a href="#products">New Arrivals</a>
        <a href="#review">Customer Reviews</a>
        <a href="#contact">Contact Us</a>
      </nav>
      <div class="icons">
        <a
          href="<?php echo is_logged_in() ? 'cart.php' : 'login.html'; ?>"
          class="fas fa-shopping-cart"
        ></a>
        <a href="login.html" class="fas fa-user"></a>
      </div>
    </header>
    <section class="home" id="home" style="background-image: url('home_background-min.jpg');">
  <div class="content">
    <h3>New Medical Supplies!</h3>
    <p>
      Our shelves are overflowing with fresh medical supplies!<br />
      We've just received a shipment of top-quality products,
      <br />ensuring we have everything you need.<br />
      From bandages and thermometers to pain relievers <br />
      and first-aid kits, we've got you covered.<br />
      Stop by today on all your essential medical supplies!
    </p>
    <a
      href="<?php echo is_logged_in() ? 'main_products.php' : 'login.html'; ?>"
      class="button"
      >Shop Now &#10132;</a
    >
  </div>
</section>
<div class="about_us reveal">
    <section class="about_us" id="about_us">
      <h1 class="heading"><span>About</span> Us</h1>
      <div class="row">
        <div class="video_container">
          <video src="about_video.mp4" loop autoplay muted></video>
          <h3>High Quality and Professionally Approved Medical Supplies</h3>
        </div>
        <div class="content">
          <h3>Why Here at MedLab?</h3>
          <p>
            MedLab is your trusted source for all your medical supply needs! We
            offer a wide selection of high-quality products at competitive
            prices, ensuring you get the best value for your health. Shop online
            with MedLab for convenient delivery right to your door, and
            experience the ease and reliability of our online platform.
          </p>
          <p>
            Choose MedLab for your medical supplies and focus on what matters
            most - your health and well-being.
          </p>
        </div>
      </div>
</div>
    </section>
    <section class="products" id="products">
      <h1 class="heading">New <span>Arrivals</span></h1>
      <div class="box_container">
        <?php
            $products = [
                ['name' =>
        '1 Box Latex Gloves', 'price' => 161.50, 'original_price' => 170,
        'discount' => 5, 'image' => 'first_product.jpg'], ['name' => '50 ML
        Sodium Chloride Vial', 'price' => 88.00, 'original_price' => 100,
        'discount' => 12, 'image' => 'second_product.jpg'], ['name' => '1 ML
        Insulin Syringe', 'price' => 13.50, 'original_price' => 15, 'discount'
        => 10, 'image' => 'third_product.jpg'], ['name' => '1 Box Tongue
        Depressors', 'price' => 114.00, 'original_price' => 120, 'discount' =>
        5, 'image' => 'fourth_product.jpg'], ['name' => '1 Box Surgical Mask',
        'price' => 72.00, 'original_price' => 90, 'discount' => 20, 'image' =>
        'fifth_product.jpg'], ['name' => '16 OZ 70% Isopropyl Alcohol', 'price'
        => 110.50, 'original_price' => 130, 'discount' => 15, 'image' =>
        'sixth_product.jpeg'], ]; foreach ($products as $product) { echo '
        <div class="box">
          '; echo '<span class="discount">' . $product['discount'] . '%</span>';
          echo '
          <div class="image">
            '; echo '<img
              src="' . $product['image'] . '"
              alt="' . $product['name'] . '"
            />'; echo '
            <div class="icons">
              '; echo '<a
                href="' . (is_logged_in() ? 'main_products.php' : 'login.html') . '"
                class="cart-btn"
                >Add to Cart</a
              >'; echo '
            </div>
            '; echo '
          </div>
          '; echo '
          <div class="content">
            '; echo '
            <h3>' . $product['name'] . '</h3>
            '; echo '
            <div class="price">
              '; echo number_format($product['price'], 2) . ' PHP'; echo '<span
                ><br />' . number_format($product['original_price'], 2) . '
                PHP</span
              >'; echo '
            </div>
            '; echo '
          </div>
          '; echo '
        </div>
        '; } ?>
      </div>
    </section>
    <section class="review" id="review">
      <h1 class="heading">Customer <span>Reviews</span></h1>
      <div class="box_container">
        <div class="box">
          <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
          </div>
          <p>
            I highly recommend MedLab to anyone looking for a convenient online
            medical supply store.
          </p>
          <div class="user">
            <img src="user_1.jpg" alt="Micah Christenson" />
            <div class="user_info">
              <h3>Micah Christenson</h3>
              <span>Happy Customer</span>
              <span class="fas fa-quote-right"></span>
            </div>
          </div>
        </div>
        <div class="box">
          <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
          </div>
          <p>Fast, Easy, Quick, and Reliable</p>
          <div class="user">
            <img src="user_2.jpg" alt="Seol Yoon A" />
            <div class="user_info">
              <h3>Seol Yoon A</h3>
              <span>Happy Customer</span>
              <span class="fas fa-quote-right"></span>
            </div>
          </div>
        </div>
        <div class="box">
          <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
          </div>
          <p>Teddy likes the good service!</p>
          <div class="user">
            <img src="user_3.jpg" alt="Rowan Atkinson" />
            <div class="user_info">
              <h3>Rowan Atkinson</h3>
              <span>Happy Customer</span>
              <span class="fas fa-quote-right"></span>
            </div>
          </div>
        </div>
      </div>
    </section>
    <section class="contact" id="contact">
      <h1 class="heading"><span>Any</span> Inquiries?</h1>
      <div class="row">
        <form
          action="mailto:info@medlab.com"
          method="post"
          enctype="text/plain"
        >
          <input
            type="text"
            name="name"
            placeholder="Enter Name"
            class="box"
            required
          />
          <input
            type="email"
            name="email"
            placeholder="Enter Email Address"
            class="box"
            required
          />
          <input
            type="tel"
            name="phone"
            placeholder="Enter Number"
            class="box"
            required
          />
          <textarea
            name="message"
            class="box"
            placeholder="Input Message"
            cols="30"
            rows="10"
            required
          ></textarea>
          <input type="submit" value="Send Message" class="button" />
        </form>
        <div class="image">
          <img src="avatar3.png" alt="Contact Avatar" />
        </div>
      </div>
    </section>
    <footer>
    <section class="footer">
      <div class="box_container">
        <div class="box">
          <h3>Useful Links</h3>
          <a href="#home">Home</a>
          <a href="#about_us">About Us</a>
          <a href="#products">Medical Products</a>
          <a href="#review">Customer Reviews</a>
          <a href="#contact">Contact Us</a>
        </div>
        <div class="box">
          <h3>Follow Our Socials</h3>
          <a href="https://www.facebook.com/profile.php?id=100082219026084"
            >Facebook</a
          >
          <a href="https://www.instagram.com/medical_laboratory_supplies/"
            >Instagram</a
          >
          <a href="https://www.tiktok.com/@inlabmedicalsupplies">Tiktok</a>
        </div>
        <div class="box">
          <h3>Shipping Locations</h3>
          <a>United States</a>
          <a>Philippines</a>
          <a>Thailand</a>
          <a>Japan</a>
          <a>China</a>
          <a>India</a>
        </div>
        <div class="box">
          <h3>Contact Informations</h3>
          <a>+639-998-765-4321</a>
          <a>medlab@business.ca</a>
          <a>Regina, Saskatchewan S4N 3S5</a>
        </div>
      </div>
      <div class="credit">
        CREATED BY <span> MEDLAB Inc.</span> || All Rights Reserve 2024
      </div>
    </section>
  </footer>
</html>

<script>
  function reveal() {
    var reveals = document.querySelectorAll(".reveal");
    for (var i = 0; i < reveals.length; i++) {
      var windowHeight = window.innerHeight;
      var elementTop = reveals[i].getBoundingClientRect().top;
      var elementVisible = 150;
      if (elementTop < windowHeight - elementVisible) {
        reveals[i].classList.add("active");
      } else {
        reveals[i].classList.remove("active");
      }
    }
  }
  window.addEventListener("scroll", reveal);
  reveal();
</script>