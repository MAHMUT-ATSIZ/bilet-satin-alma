<?php
$pageTitle = $pageTitle ?? 'ATSBİLET';
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>

  <!-- Bootstrap 5 & Icons CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root { --brand-grad: linear-gradient(135deg,#0d6efd, #6f42c1); }
    body { background: #f7f8fb; }
    .hero {
      background: var(--brand-grad);
      color: #fff; border-radius: 1rem;
      padding: 32px 24px; margin: 12px 0 24px 0;
    }
    .hero .lead { opacity:.95 }
    .card-hover:hover { transform: translateY(-2px); box-shadow: 0 0.75rem 1.25rem rgba(0,0,0,.08);}
    .seat-badge { min-width: 38px; display:inline-block; text-align:center }
  </style>
</head>
<body>

<?php include VIEW_PATH . '/partials/nav.php'; ?>

<main class="container py-3">
