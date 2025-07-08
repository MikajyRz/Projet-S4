<?php

class Template {
    private $title;
    private $content;
    private $additionalCSS = '';
    private $additionalJS = '';
    
    public function __construct($title = 'Gestion des Prêts') {
        $this->title = $title;
    }
    
    public function setContent($content) {
        $this->content = $content;
    }
    
    public function addCSS($css) {
        $this->additionalCSS .= $css;
    }
    
    public function addJS($js) {
        $this->additionalJS .= $js;
    }
    
    public function render() {
        ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->title; ?></title>
    <link rel="stylesheet" href="assets/css/theme-green.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <?php if ($this->additionalCSS): ?>
    <style><?php echo $this->additionalCSS; ?></style>
    <?php endif; ?>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-logo">
            <span>🏦</span> Prêts Système
        </div>
        <div class="navbar-menu">
            <a href="dashboard.php" class="navbar-link<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? ' active' : ''; ?>">Tableau de Bord</a>
            <a href="clients.php" class="navbar-link<?php echo basename($_SERVER['PHP_SELF']) == 'clients.php' ? ' active' : ''; ?>">Clients</a>
            <a href="fonds.php" class="navbar-link<?php echo basename($_SERVER['PHP_SELF']) == 'fonds.php' ? ' active' : ''; ?>">Fonds</a>
            <a href="prets.php" class="navbar-link<?php echo basename($_SERVER['PHP_SELF']) == 'prets.php' ? ' active' : ''; ?>">Prêts</a>
            <a href="type-prets.php" class="navbar-link<?php echo basename($_SERVER['PHP_SELF']) == 'type-prets.php' ? ' active' : ''; ?>">Types</a>
            <a href="remboursements.php" class="navbar-link<?php echo basename($_SERVER['PHP_SELF']) == 'remboursements.php' ? ' active' : ''; ?>">Remboursements</a>
            <a href="statistiques.php" class="navbar-link<?php echo basename($_SERVER['PHP_SELF']) == 'statistiques.php' ? ' active' : ''; ?>">Statistiques</a>
        </div>
        <div class="navbar-profile">
            <div class="navbar-avatar">EF</div>
        </div>
    </nav>
    <div class="main-content">
        <?php echo $this->content; ?>
    </div>
    <script src="assets/js/main.js"></script>
    <?php if ($this->additionalJS): ?>
    <script><?php echo $this->additionalJS; ?></script>
    <?php endif; ?>
</body>
</html>
        <?php
    }
} 