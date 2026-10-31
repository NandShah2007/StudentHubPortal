<?php
require_once __DIR__ . '/includes/auth.php';

$errors = [];
$successMessage = '';
$name = $email = $subject = $message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '') {
        $errors['name'] = 'Please enter your name.';
    } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 80) {
        $errors['name'] = 'Name must be between 2 and 80 characters.';
    } elseif (!preg_match("/^[\p{L}\p{M}][\p{L}\p{M} .'-]*$/u", $name)) {
        $errors['name'] = 'Please enter a valid name.';
    }

    if ($email === '') {
        $errors['email'] = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    } elseif (strlen($email) > 254) {
        $errors['email'] = 'Email address is too long.';
    }

    if ($subject === '') {
        $errors['subject'] = 'Please enter a subject.';
    } elseif (mb_strlen($subject) > 120) {
        $errors['subject'] = 'Subject must be 120 characters or fewer.';
    }

    if ($message === '') {
        $errors['message'] = 'Please enter your message.';
    } elseif (mb_strlen($message) < 5 || mb_strlen($message) > 2000) {
        $errors['message'] = 'Message must be between 5 and 2000 characters.';
    }

    if (!$errors) {
        // Messages are stored in data/contact_submissions.csv
        // (the data/ folder is blocked from the web by data/.htaccess).
        $csvFile = __DIR__ . '/data/contact_submissions.csv';
        $file = @fopen($csvFile, 'c+');

        if ($file === false) {
            $errors['storage'] = 'Unable to open the storage file. Please try again later.';
        } elseif (!flock($file, LOCK_EX)) {
            fclose($file);
            $errors['storage'] = 'Unable to lock the storage file. Please try again.';
        } else {
            fseek($file, 0, SEEK_END);
            $isEmpty = ftell($file) === 0;
            $saved = true;

            // Stop spreadsheet formula injection when an admin opens the CSV in Excel.
            $safe = function ($v) {
                return preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v;
            };

            if ($isEmpty) {
                $saved = fputcsv($file, ['Date', 'Name', 'Email', 'Subject', 'Message']) !== false;
            }
            if ($saved) {
                $saved = fputcsv($file, [
                    date('Y-m-d H:i:s'), $safe($name), $safe($email), $safe($subject), $safe($message)
                ]) !== false;
            }
            fflush($file);
            flock($file, LOCK_UN);
            fclose($file);

            if ($saved) {
                $successMessage = 'Your message was submitted successfully!';
                $name = $email = $subject = $message = '';
            } else {
                $errors['storage'] = 'The submission could not be saved. Please try again.';
            }
        }
    }
}

$pageTitle = 'Contact';
$activeNav = 'contact.php';
require __DIR__ . '/includes/header.php';
?>
<section class="card medium">
    <h1 class="page-title">Contact Student Hub</h1>
    <p class="lead">Send us your question or feedback.</p>

    <?php if ($successMessage !== ''): ?>
        <div class="alert success" role="status"><?= h($successMessage) ?></div>
    <?php endif; ?>
    <?php if (isset($errors['storage'])): ?>
        <div class="alert error" role="alert"><?= h($errors['storage']) ?></div>
    <?php endif; ?>

    <form action="contact.php" method="POST" novalidate>
        <?= csrf_field() ?>

        <label for="name">Full Name *</label>
        <input type="text" id="name" name="name" value="<?= h($name) ?>" maxlength="80" autocomplete="name" required
               aria-invalid="<?= isset($errors['name']) ? 'true' : 'false' ?>"
               <?= isset($errors['name']) ? 'aria-describedby="nameError"' : '' ?>>
        <?php if (isset($errors['name'])): ?><small class="field-error" id="nameError"><?= h($errors['name']) ?></small><?php endif; ?>

        <label for="email">Email Address *</label>
        <input type="email" id="email" name="email" value="<?= h($email) ?>" maxlength="254" autocomplete="email" required
               aria-invalid="<?= isset($errors['email']) ? 'true' : 'false' ?>"
               <?= isset($errors['email']) ? 'aria-describedby="emailError"' : '' ?>>
        <?php if (isset($errors['email'])): ?><small class="field-error" id="emailError"><?= h($errors['email']) ?></small><?php endif; ?>

        <label for="subject">Subject *</label>
        <input type="text" id="subject" name="subject" value="<?= h($subject) ?>" maxlength="120" required
               aria-invalid="<?= isset($errors['subject']) ? 'true' : 'false' ?>"
               <?= isset($errors['subject']) ? 'aria-describedby="subjectError"' : '' ?>>
        <?php if (isset($errors['subject'])): ?><small class="field-error" id="subjectError"><?= h($errors['subject']) ?></small><?php endif; ?>

        <label for="message">Message *</label>
        <textarea id="message" name="message" rows="5" maxlength="2000" required
                  aria-invalid="<?= isset($errors['message']) ? 'true' : 'false' ?>"
                  <?= isset($errors['message']) ? 'aria-describedby="messageError"' : '' ?>><?= h($message) ?></textarea>
        <?php if (isset($errors['message'])): ?><small class="field-error" id="messageError"><?= h($errors['message']) ?></small><?php endif; ?>

        <button type="submit" class="btn block">Submit Message</button>
        <p class="hint">* Required fields</p>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
