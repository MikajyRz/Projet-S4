<div class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <div class="logo-icon"></div>
            Gestion Prêts
        </div>
    </div>

    <div class="workspace-section">
        <div class="workspace-label">Workspace</div>
        <div class="workspace-name">
            Système de Prêts
            <span style="font-size: 12px;">⌄</span>
        </div>
    </div>

    <nav class="nav-menu">
        <a href="dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <div class="nav-icon">📊</div>
            Tableau de Bord
        </a>
        <a href="index.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <div class="nav-icon">🏠</div>
            Accueil
        </a>
        <a href="clients.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'clients.php' ? 'active' : ''; ?>">
            <div class="nav-icon">👥</div>
            Gestion des Clients
        </a>
        <a href="fonds.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'fonds.php' ? 'active' : ''; ?>">
            <div class="nav-icon">💰</div>
            Gestion des Fonds
        </a>
        <a href="type-prets.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'type-prets.php' ? 'active' : ''; ?>">
            <div class="nav-icon">🏦</div>
            Types de Prêts
        </a>
        <a href="prets.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'prets.php' ? 'active' : ''; ?>">
            <div class="nav-icon">💼</div>
            Gestion des Prêts
        </a>
        <a href="remboursements.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'remboursements.php' ? 'active' : ''; ?>">
            <div class="nav-icon">📊</div>
            Remboursements
        </a>
        <a href="statistiques.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'statistiques.php' ? 'active' : ''; ?>">
            <div class="nav-icon">📈</div>
            Statistiques
        </a>
    </nav>
</div> 