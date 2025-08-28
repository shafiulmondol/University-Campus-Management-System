<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SKST University - Notices</title>
  <link rel="icon" href="picture/SKST.png" type="image/png" />
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f9f9ff;
      color: #333;
      line-height: 1.6;
      padding: 20px;
    }

    

    /* Notice Section Styles */
    .content {
      margin: 30px auto;
      padding: 0 20px;
    }

    .notices-container {
      display: block;
      background: white;
      border-radius: 15px;
      box-shadow: 0 5px 25px rgba(0,0,0,0.08);
      padding: 25px;
      margin-bottom: 30px;
    }

    .notices-heading {
      text-align: center;
      color: #2c3e50;
      margin-bottom: 30px;
      font-size: 28px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }

    .notice-card {
      background: #f8f9ff;
      border-left: 5px solid #6a11cb;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 20px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .notice-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px red;
    }

    .notice-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      flex-wrap: wrap;
      gap: 10px;
    }

    .notice-title {
      color: #2c3e50;
      font-size: 20px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .notice-section {
      background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
      color: white;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 14px;
      font-weight: 500;
    }

    .notice-content {
      color: #34495e;
      margin-bottom: 15px;
      line-height: 1.6;
      white-space: pre-line;
    }

    .notice-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 15px;
      font-size: 14px;
      color: #7f8c8d;
      
      padding-top: 15px;
    }

    .notice-author, .notice-date {
      display: flex;
      align-items: center;
      gap: 5px;
    }

.back-button-container {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 9999; /* keep it above other elements */
}



    .back-button {
  background: #6a11cb; /* purple gradient base */
  color: white;
  text-decoration: none;
  border-radius: 8px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s ease;
}

.back-button:hover {
  background: #2575fc; /* hover color */
  transform: translateY(-3px);
  box-shadow: 0 5px 15px rgba(106, 17, 203, 0.4);
}

    .no-notices {
      text-align: center;
      padding: 50px 20px;
      background: white;
      border-radius: 15px;
      box-shadow: 0 5px 25px rgba(0,0,0,0.08);
    }

    .no-notices i {
      font-size: 60px;
      color: #ddd;
      margin-bottom: 20px;
    }

    .no-notices p {
      font-size: 18px;
      color: #7f8c8d;
      margin-bottom: 30px;
    }

    @media (max-width: 768px) {
      .navbar-top {
        flex-direction: column;
        align-items: center;
      }

      .btn {
        width: 80%;
      }

      .menu-section {
        flex-direction: column;
        align-items: center;
      }

      .home-button {
        margin-top: 10px;
      }
      
      .notice-header {
        flex-direction: column;
        align-items: flex-start;
      }
      
      .notice-footer {
        flex-direction: column;
        align-items: flex-start;
      }
    }
  </style>
</head>
<body>

  <div class="content">
    <?php
    // Database connection
   require_once '../library/notice.php';
   echo see_notice();
    ?>
  </div>
</body>
</html>