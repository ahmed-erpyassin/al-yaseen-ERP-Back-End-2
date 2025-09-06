<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>Material Design for Bootstrap</title>
    <!-- MDB icon -->
    <link rel="icon" href="../../img/mdb-favicon.ico" type="image/x-icon" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <!-- Google Fonts Roboto -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" />
    <!-- MDB -->
    <link rel="stylesheet" href="../../css/mdb.min.css" />
    <!-- PRISM -->
    <link rel="stylesheet" href="../../dev/css/new-prism.css" />
    <!-- Custom styles -->
    <style>
        @media (min-width: 1400px) {

            main,
            header,
            #main-navbar {
                padding-left: 240px;
            }
        }
    </style>
</head>

<body>
    <!-- Sidenav -->
    <nav id="sidenav-1" class="sidenav sidenav-sm" data-mdb-hidden="false" data-mdb-accordion="true">

        <a class="ripple d-flex justify-content-center py-4 mb-3" style="border-bottom: 2px solid #f5f5f5"
            href="#!" data-mdb-ripple-color="primary">
            <img id="MDB-logo" src="https://mdbcdn.b-cdn.net/wp-content/uploads/2018/06/logo-mdb-jquery-small.webp"
                alt="MDB Logo" draggable="false" />
        </a>

        <ul class="sidenav-menu px-2 pb-5">

            <li class="sidenav-item">
                <a class="sidenav-link" href="">
                    <i class="fas fa-tachometer-alt fa-fw me-3"></i><span>Overview</span></a>
            </li>

            <li class="sidenav-item pt-3">
                <span class="sidenav-subheading text-muted">Create</span>
                <a class="sidenav-link" href="">
                    <i class="fas fa-plus fa-fw me-3"></i><span>Project</span></a>
            </li>
            <li class="sidenav-item">
                <a class="sidenav-link" href="">
                    <i class="fas fa-plus fa-fw me-3"></i><span>Database</span></a>
            </li>

            <li class="sidenav-item pt-3">
                <span class="sidenav-subheading text-muted">Manage</span>
                <a class="sidenav-link" href="">
                    <i class="fas fa-cubes fa-fw me-3"></i><span>Projects</span></a>
            </li>
            <li class="sidenav-item">
                <a class="sidenav-link" href="">
                    <i class="fas fa-database fa-fw me-3"></i><span>Databases</span></a>
            </li>
            <li class="sidenav-item">
                <a class="sidenav-link" href="">
                    <i class="fas fa-stream fa-fw me-3"></i><span>Custom domains</span></a>
            </li>
            <li class="sidenav-item">
                <a class="sidenav-link" href="">
                    <i class="fas fa-code-branch fa-fw me-3"></i><span>Repositories</span></a>
            </li>
            <li class="sidenav-item">
                <a class="sidenav-link" href="">
                    <i class="fas fa-users fa-fw me-3"></i><span>Team</span></a>
            </li>

            <li class="sidenav-item pt-3">
                <span class="sidenav-subheading text-muted">Maintain</span>
                <a class="sidenav-link" href="">
                    <i class="fas fa-chart-pie fa-fw me-3"></i><span>Analytics</span></a>
            </li>
            <li class="sidenav-item">
                <a class="sidenav-link" href="">
                    <i class="fas fa-sync fa-fw me-3"></i><span>Backups</span></a>
            </li>
            <li class="sidenav-item">
                <a class="sidenav-link" href="">
                    <i class="fas fa-shield-alt fa-fw me-3"></i><span>Security</span></a>
            </li>

            <li class="sidenav-item pt-3">
                <span class="sidenav-subheading text-muted">Admin</span>
                <a class="sidenav-link" href="">
                    <i class="fas fa-money-bill fa-fw me-3"></i><span>Billing</span></a>
            </li>
            <li class="sidenav-item">
                <a class="sidenav-link" href="">
                    <i class="fas fa-file-contract fa-fw me-3"></i><span>License</span></a>
            </li>

            <li class="sidenav-item pt-3">
                <span class="sidenav-subheading text-muted">Tools</span>
                <a class="sidenav-link" href="">
                    <i class="fas fa-hand-pointer fa-fw me-3"></i><span>Drag & drop builder</span></a>
            </li>
            <li class="sidenav-item">
                <a class="sidenav-link" href="">
                    <i class="fas fa-code fa-fw me-3"></i><span>Online code editor</span></a>
            </li>
            <li class="sidenav-item">
                <a class="sidenav-link" href="">
                    <i class="fas fa-copy fa-fw me-3"></i><span>SFTP</span></a>
            </li>
            <li class="sidenav-item">
                <a class="sidenav-link" href="">
                    <i class="fab fa-jenkins fa-fw me-3"></i><span>Jenkins</span></a>
            </li>
            <li class="sidenav-item">
                <a class="sidenav-link" href="">
                    <i class="fab fa-gitlab fa-fw me-3"></i><span>GitLab</span></a>
            </li>
        </ul>
    </nav>
    <!-- Sidenav -->

    <!-- Toggler -->
    <button data-mdb-toggle="sidenav" data-mdb-target="#sidenav-1" class="btn btn-primary"
        aria-controls="#sidenav-1" aria-haspopup="true">
        <i class="fas fa-bars"></i>
    </button>
    <!-- Toggler -->

    <!-- PRISM -->
    <script type="text/javascript" src="../../dev/js/new-prism.js"></script>
    <!-- MDB SNIPPET -->
    <script type="text/javascript" src="../../dev/js/dist/mdbsnippet.min.js"></script>
    <!-- MDB -->
    <script type="text/javascript" src="../../js/mdb.min.js"></script>
    <!-- Custom scripts -->
    <script type="text/javascript">
        const sidenav = document.getElementById('sidenav-1');
        const sidenavInstance = mdb.Sidenav.getInstance(sidenav);

        let innerWidth = null;

        const setMode = (e) => {
            // Check necessary for Android devices
            if (window.innerWidth === innerWidth) {
                return;
            }

            innerWidth = window.innerWidth;

            if (window.innerWidth < 1400) {
                sidenavInstance.changeMode('over');
                sidenavInstance.hide();
            } else {
                sidenavInstance.changeMode('side');
                sidenavInstance.show();
            }
        };

        setMode();

        // Event listeners
        window.addEventListener('resize', setMode);
    </script>
</body>

</html>
