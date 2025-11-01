<?php 
// daycare/enrollment_form.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Daycare Registration Form</title>
  <style>
    :root {
      --primary: #4a6fa5;
      --secondary: #6b8cbc;
      --bg: #f5f7fa;
      --text: #333;
      --card-bg: #ffffff;
      --border: #e1e6ed;
      --shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
      --radius: 10px;
    }

    * {
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background-color: var(--bg);
      margin: 0;
      padding: 30px;
      color: var(--text);
    }

    .container {
      max-width: 900px;
      margin: 0 auto;
      background-color: var(--card-bg);
      padding: 40px 50px;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      border: 1px solid var(--border);
    }

    h1 {
      color: var(--primary);
      text-align: center;
      margin-bottom: 25px;
      font-size: 2rem;
    }

    h2 {
      color: var(--secondary);
      border-left: 4px solid var(--primary);
      padding-left: 10px;
      margin-top: 40px;
      font-size: 1.3rem;
      margin-bottom: 20px;
    }

    h3 {
      color: var(--primary);
      font-size: 1.1rem;
      margin-top: 0;
    }

    .section-card {
      background: #f9fbfe;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 25px 30px;
      margin-bottom: 30px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-bottom: 6px;
      font-weight: 600;
      color: #444;
      font-size: 0.95rem;
    }

    input, select {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 15px;
      transition: border-color 0.2s, box-shadow 0.2s;
    }

    input:focus, select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(74, 111, 165, 0.15);
      outline: none;
    }

    .radio-group {
      display: flex;
      gap: 25px;
      flex-wrap: wrap;
    }

    .radio-option {
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .name-group {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
    }

    .parent-info {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 25px;
    }

    .submit-btn {
      background: var(--primary);
      color: white;
      border: none;
      padding: 15px 25px;
      font-size: 17px;
      border-radius: 8px;
      cursor: pointer;
      width: 100%;
      margin-top: 25px;
      font-weight: 600;
      letter-spacing: 0.5px;
      transition: all 0.3s ease;
    }

    .submit-btn:hover {
      background: #3b5c8a;
      transform: translateY(-1px);
    }

    @media (max-width: 600px) {
      body {
        padding: 15px;
      }

      .container {
        padding: 25px;
      }

      h1 {
        font-size: 1.6rem;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Daycare Registration Form</h1>

    <form id="enrollmentForm">
      <div class="section-card">
        <h2>Child Information</h2>
        <div class="form-group">
          <label for="childFirstName">First Name</label>
          <input type="text" id="childFirstName" name="childFirstName" required>
        </div>
        <div class="form-group">
          <label for="childMiddleName">Middle Name</label>
          <input type="text" id="childMiddleName" name="childMiddleName">
        </div>
        <div class="form-group">
          <label for="childLastName">Last Name</label>
          <input type="text" id="childLastName" name="childLastName" required>
        </div>
        <div class="form-group">
          <label>Sex</label>
          <div class="radio-group">
            <div class="radio-option">
              <input type="radio" id="male" name="sex" value="male" required>
              <label for="male">Male</label>
            </div>
            <div class="radio-option">
              <input type="radio" id="female" name="sex" value="female">
              <label for="female">Female</label>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label for="address">Address</label>
          <input type="text" id="address" name="address" required>
        </div>
        <div class="form-group">
          <label for="birthday">Birthday</label>
          <input type="date" id="birthday" name="birthday" required>
        </div>
        <div class="form-group">
          <label for="guardian">Guardian</label>
          <input type="text" id="guardian" name="guardian" required>
        </div>
        <div class="form-group">
          <label for="relationship">Relationship to Child</label>
          <input type="text" id="relationship" name="relationship" required>
        </div>
        <div class="form-group">
          <label for="firstLanguage">Child's First Language</label>
          <input type="text" id="firstLanguage" name="firstLanguage" required>
        </div>
        <div class="form-group">
          <label for="secondaryLanguage">Child's Secondary Language</label>
          <input type="text" id="secondaryLanguage" name="secondaryLanguage">
        </div>
      </div>

      <div class="section-card">
        <h2>Guardian Information</h2>
        <div class="form-group">
          <label for="guardianName">Full Name</label>
          <input type="text" id="guardianName" name="guardianName" required>
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" required>
        </div>

        <div class="parent-info">
          <div>
            <h3>Mother's Information</h3>
            <div class="form-group"><label for="motherName">Full Name</label><input type="text" id="motherName" name="motherName"></div>
            <div class="form-group"><label for="motherAddress">Address</label><input type="text" id="motherAddress" name="motherAddress"></div>
            <div class="form-group"><label for="motherOccupation">Occupation</label><input type="text" id="motherOccupation" name="motherOccupation"></div>
            <div class="form-group"><label for="motherContact">Contact Number</label><input type="tel" id="motherContact" name="motherContact"></div>
          </div>

          <div>
            <h3>Father's Information</h3>
            <div class="form-group"><label for="fatherName">Full Name</label><input type="text" id="fatherName" name="fatherName"></div>
            <div class="form-group"><label for="fatherAddress">Address</label><input type="text" id="fatherAddress" name="fatherAddress"></div>
            <div class="form-group"><label for="fatherOccupation">Occupation</label><input type="text" id="fatherOccupation" name="fatherOccupation"></div>
            <div class="form-group"><label for="fatherContact">Contact Number</label><input type="tel" id="fatherContact" name="fatherContact"></div>
          </div>
        </div>
      </div>

      <div class="section-card">
        <h2>Emergency Contact</h2>
        <div class="form-group"><label for="emergencyName">Full Name</label><input type="text" id="emergencyName" name="emergencyName" required></div>
        <div class="form-group"><label for="emergencyRelationship">Relationship to Child</label><input type="text" id="emergencyRelationship" name="emergencyRelationship" required></div>
        <div class="form-group"><label for="emergencyContact">Contact Number</label><input type="tel" id="emergencyContact" name="emergencyContact" required></div>
        <div class="form-group"><label for="emergencyOccupation">Occupation</label><input type="text" id="emergencyOccupation" name="emergencyOccupation"></div>
      </div>

      <button type="submit" class="submit-btn">Submit Enrollment Form</button>
    </form>
  </div>

  <script>
document.getElementById('enrollmentForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const formData = new FormData(this);

  fetch('enrollment-backend.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert('✅ ' + data.message);
      document.getElementById('enrollmentForm').reset();
    } else {
      alert('❌ ' + data.message);
    }
  })
  .catch(error => alert('⚠️ An error occurred: ' + error));
});
</script>

</body>
</html>
