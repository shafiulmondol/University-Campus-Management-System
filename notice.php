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
    }

    .navbar {
      background-color: #e0e7ff;
      padding: 10px 20px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .navbar-top {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .logo img {
      height: 80px;
    }

    .logo h1 {
      font-size: 26px;
      color: #333;
    }

    .home-button {
      background: gray;
      color: white;
      border: none;
      padding: 10px 16px;
      font-size: 15px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
    }

    .home-button:hover {
      transform: translateY(-3px);
      background: linear-gradient(135deg, #18bcae, #f3af02);
    }

    .menu-section {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 10px;
      margin-top: 15px;
    }

    .menu-section a {
      text-decoration: none;
    }

    .btn {
      background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
      color: white;
      border: none;
      padding: 12px 20px;
      font-size: 15px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
      min-width: 120px;
      cursor: pointer;
    }

    .btn:hover {
      transform: translateY(-3px);
      background: linear-gradient(135deg, #512da8, #1e88e5);
    }

    /* Notice Section Styles */
    .content {
      max-width: 1200px;
      margin: 30px auto;
      padding: 0 20px;
    }

    .notices-container {
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
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
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
      border-top: 1px solid #eee;
      padding-top: 15px;
    }

    .notice-author, .notice-date {
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .back-button-container {
      text-align: center;
      margin-top: 30px;
    }

    .back-button {
      background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
      color: white;
      text-decoration: none;
      padding: 12px 25px;
      border-radius: 8px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.3s ease;
    }

    .back-button:hover {
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
   require_once 'library/notice.php';
   echo see_notice();
    ?>
  </div>
</body>
</html>