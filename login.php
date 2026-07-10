<?php
session_start();

// If already verified, skip straight to the generator
if (!empty($_SESSION['verified_email'])) {
    header('Location: certificate.php');
    exit;
}

$error = '';

/**
 * Load the attendee list from attendees.csv.
 * Expected format: email,name  (header row required)
 * Returns an associative array: [lowercase_email => name]
 */
function loadAttendees(string $file): array {
    $attendees = [];
    if (!is_readable($file)) {
        return $attendees;
    }
    if (($handle = fopen($file, 'r')) !== false) {
        fgetcsv($handle); // skip header row
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 1 && trim($row[0]) !== '') {
                $email = strtolower(trim($row[0]));
                $name  = isset($row[1]) ? trim($row[1]) : '';
                $attendees[$email] = $name;
            }
        }
        fclose($handle);
    }
    return $attendees;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted = strtolower(trim($_POST['email'] ?? ''));

    if ($submitted === '' || !filter_var($submitted, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $attendees = loadAttendees(__DIR__ . '/attendees.csv');

        if (array_key_exists($submitted, $attendees)) {
            // Regenerate the session id on login to avoid session fixation
            session_regenerate_id(true);
            $_SESSION['verified_email'] = $submitted;
            $_SESSION['attendee_name']  = $attendees[$submitted];
            header('Location: certificate.php');
            exit;
        } else {
            $error = "We couldn't find that email on the SQA Festival 2026 attendee list. Double-check it, or contact the organisers if you believe this is a mistake.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SQA Festival 2026 — Verify Your Email</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
:root{
  --navy:#0a1628;--navy-mid:#112040;--navy-light:#1a3060;
  --gold:#c9a84c;--gold-light:#e8c96a;
  --white:#ffffff;--gray-300:#c8d0dc;--gray-500:#6b7a94;
  --radius-sm:6px;
}
body{
  min-height:100vh;
  display:flex;
  align-items:center;
  justify-content:center;
  background:var(--navy);
  font-family:'Inter',sans-serif;
  color:var(--white);
  padding:20px;
}
.card{
  width:100%;
  max-width:420px;
  background:var(--navy-mid);
  border:1px solid rgba(201,168,76,.25);
  border-radius:12px;
  padding:36px 32px;
  box-shadow:0 24px 60px rgba(0,0,0,.5);
}
.badge{
  display:inline-block;
  background:linear-gradient(135deg,var(--gold),var(--gold-light));
  color:var(--navy);
  font-size:10px;
  font-weight:700;
  letter-spacing:.15em;
  text-transform:uppercase;
  padding:4px 10px;
  border-radius:20px;
  margin-bottom:16px;
}
h1{
  font-family:'Playfair Display',serif;
  font-size:24px;
  font-weight:600;
  margin-bottom:8px;
}
p.lead{
  color:var(--gray-300);
  font-size:13.5px;
  line-height:1.6;
  margin-bottom:26px;
}
label{
  display:block;
  font-size:12px;
  font-weight:500;
  color:var(--gray-300);
  margin-bottom:6px;
}
input[type="email"]{
  width:100%;
  background:rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.12);
  border-radius:var(--radius-sm);
  color:var(--white);
  font-family:'Inter',sans-serif;
  font-size:14px;
  padding:12px 14px;
  outline:none;
  transition:border-color .2s;
}
input[type="email"]:focus{ border-color:var(--gold); }
.btn-primary{
  width:100%;
  margin-top:16px;
  background:linear-gradient(135deg,var(--gold),var(--gold-light));
  color:var(--navy);
  border:none;
  border-radius:var(--radius-sm);
  font-family:'Inter',sans-serif;
  font-size:14px;
  font-weight:700;
  letter-spacing:.04em;
  padding:13px;
  cursor:pointer;
  transition:opacity .2s;
}
.btn-primary:hover{ opacity:.9; }
.error{
  margin-top:14px;
  background:rgba(228,87,46,.12);
  border:1px solid rgba(228,87,46,.4);
  color:#ffb7a3;
  font-size:12.5px;
  line-height:1.5;
  padding:11px 13px;
  border-radius:var(--radius-sm);
}
.footnote{
  margin-top:22px;
  font-size:11.5px;
  color:var(--gray-500);
  text-align:center;
  line-height:1.6;
}
</style>
</head>
<body>

<div class="card">
  <span class="badge">SQA Festival 2026</span>
  <h1>Get your certificate</h1>
  <p class="lead">Enter the email address you used to register for the Software Quality Assurance Festival 2026. We'll check it against the attendee list before letting you in.</p>

  <form method="POST" action="login.php">
    <label for="email">Email address</label>
    <input type="email" id="email" name="email" placeholder="you@example.com" required autofocus value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES); ?>">
    <button type="submit" class="btn-primary">Continue</button>
  </form>

  <?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES); ?></div>
  <?php endif; ?>

  <p class="footnote">Registered with a different email? Contact the organisers at Kiwami Tech Solutions.</p>
</div>

</body>
</html>