<?php
$seccion = $_GET['seccion'] ?? 'inicio';
$seccionesValidas = ['inicio', 'caracteristicas', 'beneficios'];

if (!in_array($seccion, $seccionesValidas, true)) {
    $seccion = 'inicio';
}

$productosCatalogo = [];
$errorCatalogo = false;
if ($seccion === 'inicio') {
    try {
        require_once __DIR__ . '/../config/database.php';
        $resultadoCatalogo = $conn->query(
            'SELECT nombre, color, categoria, descripcion, imagen
             FROM productos
             WHERE estado = 1
             ORDER BY id DESC'
        );
        if (!$resultadoCatalogo) {
            throw new RuntimeException('No se pudo consultar el catálogo público de productos.');
        }
        $productosCatalogo = $resultadoCatalogo->fetch_all(MYSQLI_ASSOC);
        $resultadoCatalogo->free();
    } catch (Throwable $exception) {
        error_log('Error al cargar el catálogo público de productos: ' . $exception->getMessage());
        $errorCatalogo = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bella Tela - Software de Gestión para Almacenes de Telas</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="img/style.css?v=<?php echo time(); ?>">
    <style>
        :root {
            --primary-ink: #1C191B;
            --primary-cta: #B9974E;
            --brand-pink: #A85F6D;
            --brand-pink-dark: #854653;
            --bg-soft: #FAF8F8;
            --text-primary: #292528;
            --text-secondary: #70696D;
            --white: #FFFFFF;
            --shadow-soft: 0 4px 20px rgba(0, 0, 0, 0.06);
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-primary);
            background: var(--bg-soft);
            overflow-x: hidden;
        }

        .navbar {
            background: #FFFFFF !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(45, 31, 41, 0.1);
            padding: 0.85rem 0;
            box-shadow: var(--shadow-soft);
        }

        .navbar .container {
            max-width: 1360px;
            padding-left: 1.2rem;
            padding-right: 1.2rem;
        }

        .navbar-brand {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: auto;
            height: auto;
            padding: 0;
            margin-right: 0.8rem;
            background: transparent;
            box-shadow: none;
            overflow: visible;
        }

        .navbar-logo {
            height: 52px;
            width: auto;
            object-fit: contain;
            display: block;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.08));
        }

        .navbar .nav-link {
            color: var(--text-primary) !important;
            font-weight: 600;
            font-size: 1rem;
            padding: 0.7rem 1rem !important;
            border-radius: 0.75rem;
            transition: all 0.2s ease;
        }

        .navbar .nav-link:hover {
            color: var(--brand-pink-dark) !important;
            background: rgba(196, 61, 120, 0.07);
        }

        .btn-primary {
            background: #A85F6D !important;
            border: none !important;
            color: #FFFFFF !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06) !important;
            font-weight: 600 !important;
            border-radius: 9999px !important;
            padding: 14px 28px !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-primary:hover {
            background: #854653 !important;
            transform: translateY(-1px);
            box-shadow: 0 6px 22px rgba(0, 0, 0, 0.1) !important;
        }

        .hero-section {
            min-height: 100vh;
            position: relative;
            overflow: hidden;
            background: #201914;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url('img/Fondo.png') center center / cover no-repeat;
            filter: brightness(0.95);
            z-index: 0;
        }

        .hero-section::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(20,10,5,0.65) 0%, rgba(20,10,5,0.25) 60%, rgba(20,10,5,0.05) 100%);
            z-index: 0;
        }

        .container {
            position: relative;
            z-index: 1;
        }

        .hero-copy {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 640px;
            margin-left: 8%;
            padding: 80px 0;
            animation: heroCopyIn 0.8s ease both;
        }

        .hero-copy h1 {
            max-width: 640px;
            font-size: 52px;
            line-height: 1.05;
            letter-spacing: -1.5px;
            color: #FFFFFF !important;
            font-weight: 600;
            margin-bottom: 0;
            text-shadow: none;
        }

        .hero-copy .line {
            display: inline;
        }

        .hero-copy .text-primary-custom {
            display: inline;
            color: #FFFFFF !important;
            font-weight: 600;
            margin-top: 0;
            text-shadow: none;
        }

        .hero-copy p {
            max-width: 500px;
            margin-top: 20px;
            margin-bottom: 0;
            font-size: 17px;
            font-weight: 400;
            line-height: 1.7;
            color: #E5E7EB !important;
            text-shadow: none;
        }

        .hero-actions {
            margin-top: 32px;
            gap: 16px !important;
        }

        .hero-secondary {
            background: rgba(255,255,255,0.12) !important;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.3) !important;
            color: #fff !important;
            box-shadow: none !important;
            border-radius: 9999px !important;
            padding: 14px 28px !important;
            font-weight: 600 !important;
        }

        .hero-secondary:hover {
            background: rgba(255,255,255,0.14) !important;
            color: #fff !important;
        }

        .hero-logo-wrap {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 520px;
            animation: heroLogoIn 0.9s ease both;
        }

        .hero-figure {
            width: min(100%, 520px);
            height: 560px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            transform: translateY(10px);
        }

        .figure-svg {
            width: 100%;
            height: auto;
            max-height: 560px;
            display: block;
            filter: drop-shadow(0 8px 12px rgba(20, 12, 10, 0.08));
        }

        @keyframes heroCopyIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes floatLogo {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes heroLogoIn {
            from { opacity: 0; transform: translateY(24px) scale(0.96); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes floatLogo {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @media (max-width: 991px) {
            .hero-section {
                min-height: auto;
                padding: 72px 0 0;
            }
            .hero-copy {
                max-width: 640px;
                margin: 0 auto;
                padding: 80px 24px;
                text-align: left !important;
            }
            .hero-copy h1 {
                font-size: 44px;
            }
            .hero-logo-wrap {
                min-height: 320px;
            }
            .hero-logo {
                max-height: 300px;
                animation: none;
            }
        }

        @media (max-width: 576px) {
            .hero-copy {
                padding: 64px 20px;
            }
            .hero-copy h1 {
                font-size: 36px;
                letter-spacing: -1px;
            }
            .hero-actions {
                align-items: stretch;
            }
            .hero-actions .btn {
                text-align: center;
            }
        }

        .beneficios-section {
            background: linear-gradient(180deg, #f5efe9 0%, #eadcd3 100%);
            padding-top: 2rem;
            padding-bottom: 2rem;
        }
        .beneficio-card {
            background: rgba(255, 252, 249, 0.76);
            border: 1px solid rgba(87, 60, 52, 0.08);
            border-radius: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 8px 22px rgba(79, 58, 53, 0.06);
            padding: 1.1rem 1.1rem 1.1rem;
            height: 100%;
            min-height: 290px;
            backdrop-filter: blur(4px);
        }
        .beneficio-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 30px rgba(79, 58, 53, 0.09);
        }
        .beneficio-header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.2rem;
        }
        .beneficio-icon {
            width: 48px; height: 48px;
            background: linear-gradient(135deg, rgba(219, 162, 134, 0.22), rgba(214, 168, 126, 0.2));
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.35rem;
            color: #4D3A35;
            border: 1px solid rgba(77, 58, 53, 0.08);
            margin: 0 auto 1rem;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.7);
        }
        .beneficio-card h5 {
            color: #2d1f1d !important;
            font-size: clamp(1.25rem, 1.8vw, 1.7rem);
            font-weight: 800;
            letter-spacing: -0.04em;
            margin-bottom: 0.75rem;
            line-height: 1.15;
        }
        .beneficio-card p {
            color: rgba(45, 31, 29, 0.82) !important;
            font-size: 0.97rem;
            line-height: 1.5;
            margin-bottom: 0;
        }

        .navbar-home {
            background: rgba(28, 25, 27, 0.88) !important;
            border-bottom: 1px solid rgba(185, 151, 78, 0.42) !important;
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.18) !important;
            backdrop-filter: blur(14px);
        }
        .navbar-home .navbar-logo {
            height: 66px;
            width: 84px;
            padding: 0;
            border-radius: 0;
            background: transparent;
            object-fit: contain;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.2));
        }
        .navbar-home .nav-link {
            color: rgba(255,255,255,0.88) !important;
        }
        .navbar-home .nav-link:hover {
            color: #E8C979 !important;
            background: rgba(255,255,255,0.08);
        }
        .navbar-home .navbar-toggler {
            border-color: rgba(255,255,255,0.4);
        }
        .navbar-home .navbar-toggler-icon {
            filter: invert(1);
        }
        .navbar-home .btn-primary {
            background: #FFFFFF !important;
            border: 1px solid #FFFFFF !important;
            color: #854653 !important;
        }
        .navbar-home .btn-primary:hover {
            background: #F5EFEF !important;
            border-color: #F5EFEF !important;
        }
        .hero-section {
            display: flex !important;
            min-height: 100vh !important;
            padding: 116px 0 48px !important;
            background: #1C191B !important;
        }
        .hero-section::before {
            display: block !important;
            background: url('img/Fondo.png') center center / cover no-repeat !important;
            filter: none !important;
            opacity: 1;
        }
        .hero-section::after {
            display: block !important;
            background:
                linear-gradient(90deg, rgba(28,25,27,0.5) 0%, rgba(28,25,27,0.34) 42%, rgba(28,25,27,0.12) 100%),
                linear-gradient(0deg, rgba(28,25,27,0.2) 0%, transparent 36%) !important;
        }
        .hero-section > .container {
            display: flex;
            align-items: center;
            width: 100% !important;
            max-width: 1360px !important;
            padding-left: clamp(24px, 6vw, 88px);
            padding-right: clamp(24px, 6vw, 88px);
        }
        .hero-section .row {
            width: 100% !important;
            margin: 0 !important;
        }
        .hero-section .hero-copy {
            flex: 0 1 760px !important;
            width: 100% !important;
            max-width: 760px !important;
            margin: 0 !important;
            padding: 50px 0 !important;
            text-align: left !important;
            animation: heroCopyIn 0.8s ease both;
        }
        .hero-eyebrow {
            display: inline-block;
            margin-bottom: 20px;
            color: #FFFFFF;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.16em;
        }
        .hero-section .hero-copy h1,
        .hero-section .hero-copy h1 span {
            max-width: 760px !important;
            color: #FFFFFF !important;
            font-family: 'Inter', sans-serif !important;
            font-size: clamp(2.8rem, 5vw, 4.5rem) !important;
            font-weight: 700 !important;
            line-height: 1.08 !important;
            letter-spacing: -0.045em !important;
            text-shadow: 0 2px 24px rgba(0,0,0,0.25) !important;
        }
        .hero-section .hero-copy h1 {
            margin-bottom: 22px !important;
        }
        .hero-section .hero-copy .line {
            display: block !important;
        }
        .hero-section .hero-copy .line {
            color: #FFFFFF !important;
        }
        .hero-section .hero-copy .text-primary-custom {
            color: #D9B6B9 !important;
        }
        body .hero-section .hero-copy h1,
        body .hero-section .hero-copy h1 span {
            color: #FFFFFF !important;
        }
        body .hero-section .hero-copy h1 .text-primary-custom {
            color: #D9B6B9 !important;
        }
        .hero-section .hero-copy p {
            max-width: 590px !important;
            margin: 0 0 30px !important;
            color: rgba(255,255,255,0.86) !important;
            font-size: clamp(1rem, 1.4vw, 1.15rem) !important;
            line-height: 1.75 !important;
        }
        body .hero-section .hero-copy p {
            color: rgba(255,255,255,0.86) !important;
        }
        .hero-section .hero-actions {
            justify-content: flex-start !important;
            gap: 14px !important;
            margin: 0 !important;
        }
        .hero-section .hero-actions .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 52px !important;
            padding: 14px 24px !important;
            border-radius: 8px !important;
            font-size: 0.96rem !important;
        }
        .hero-section .hero-actions .btn-primary {
            background: #B9974E !important;
            border: 1px solid #B9974E !important;
            color: #211B16 !important;
            box-shadow: 0 8px 24px rgba(0,0,0,0.18) !important;
        }
        .hero-section .hero-actions .btn-primary:hover {
            background: #D0B365 !important;
            border-color: #D0B365 !important;
        }
        .hero-section .hero-actions .hero-secondary {
            background: rgba(255,255,255,0.06) !important;
            border: 1px solid rgba(255,255,255,0.55) !important;
            color: #FFFFFF !important;
        }
        .hero-section .hero-actions .hero-secondary:hover {
            background: rgba(255,255,255,0.15) !important;
        }
        .hero-capabilities {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0;
            margin-top: 48px;
            border-top: 1px solid rgba(255,255,255,0.24);
            padding-top: 22px;
        }
        .hero-capability {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 9px;
            min-width: 0;
            padding: 0 22px;
            color: rgba(255,255,255,0.9);
        }
        .hero-capability__icon {
            display: block;
            margin-bottom: 2px;
            color: #E8C979;
            font-size: 1.55rem;
        }
        .hero-capability strong {
            color: #FFFFFF;
            font-size: 0.96rem;
            font-weight: 650;
            line-height: 1.35;
        }
        .hero-capability span {
            max-width: 245px;
            color: rgba(255,255,255,0.78);
            font-size: 0.81rem;
            font-weight: 400;
            line-height: 1.55;
        }
        .hero-capability:first-child {
            padding-left: 0;
        }
        .hero-capability + .hero-capability {
            border-left: 1px solid rgba(255,255,255,0.2);
        }
        .home-capabilities-section {
            background: #1C191B;
            border-top: 1px solid rgba(185,151,78,0.42);
        }
        .home-capabilities-section .hero-capabilities {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0;
            border-top: 0;
        }
        .catalog-section {
            padding: 76px 0 84px;
            background: linear-gradient(180deg, #FBF7F5 0%, #FFFFFF 100%);
        }
        .catalog-eyebrow {
            color: #A85F6D;
            font-size: .76rem;
            font-weight: 700;
            letter-spacing: .16em;
        }
        .catalog-heading {
            color: #1C191B;
            font-size: clamp(2rem, 3.2vw, 2.8rem);
            font-weight: 700;
            letter-spacing: -.04em;
        }
        .catalog-intro {
            max-width: 680px;
            color: #70696D;
            line-height: 1.7;
        }
        .catalog-card {
            height: 100%;
            overflow: hidden;
            border: 1px solid rgba(28,25,27,.09);
            border-radius: 16px;
            background: #FFFFFF;
            box-shadow: 0 10px 30px rgba(28,25,27,.07);
            transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
        }
        .catalog-card:hover {
            transform: translateY(-4px);
            border-color: rgba(185,151,78,.55);
            box-shadow: 0 16px 36px rgba(28,25,27,.12);
        }
        .catalog-image-wrap {
            display: grid;
            aspect-ratio: 4 / 3;
            place-items: center;
            overflow: hidden;
            background: #F5EFEF;
        }
        .catalog-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .catalog-image-placeholder {
            color: #A85F6D;
            font-size: 2.5rem;
        }
        .catalog-card-body {
            padding: 20px;
        }
        .catalog-category {
            display: inline-block;
            margin-bottom: 9px;
            color: #854653;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }
        .catalog-product-name {
            margin-bottom: 8px;
            color: #1C191B;
            font-size: 1.12rem;
            font-weight: 700;
        }
        .catalog-product-color {
            margin-bottom: 10px;
            color: #70696D;
            font-size: .88rem;
        }
        .catalog-product-description {
            display: -webkit-box;
            overflow: hidden;
            margin-bottom: 0;
            color: #70696D;
            font-size: .88rem;
            line-height: 1.6;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
        }
        .catalog-empty {
            padding: 34px 20px;
            border: 1px dashed rgba(168,95,109,.35);
            border-radius: 16px;
            color: #70696D;
            background: rgba(255,255,255,.72);
        }
        @media (max-width: 576px) {
            .catalog-section {
                padding: 54px 0 60px;
            }
        }
        .features-section {
            position: relative;
            isolation: isolate;
            min-height: calc(100vh - 80px);
            background:
                linear-gradient(115deg, rgba(28,25,27,0.94), rgba(28,25,27,0.78)),
                url('img/Fondo.png') center center / cover no-repeat !important;
        }
        .features-section h2 {
            color: #FFFFFF !important;
            font-size: clamp(2rem, 3.5vw, 3rem);
            letter-spacing: -0.04em;
        }
        .features-section .container > .text-center > p {
            color: rgba(255,255,255,0.82) !important;
        }
        .features-section .feature-card {
            min-height: 280px;
            padding: 28px !important;
            border: 1px solid rgba(185,151,78,0.32) !important;
            border-radius: 18px !important;
            background: rgba(35,31,34,0.84) !important;
            box-shadow: 0 16px 36px rgba(0,0,0,0.2) !important;
            backdrop-filter: blur(10px);
            transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
        }
        .features-section .feature-card:hover {
            transform: translateY(-5px);
            border-color: rgba(232,201,121,0.72) !important;
            box-shadow: 0 20px 42px rgba(0,0,0,0.3) !important;
        }
        .features-section .feature-icon {
            width: 58px;
            height: 58px;
            border: 1px solid rgba(232,201,121,0.34);
            border-radius: 15px !important;
            background: rgba(168,95,109,0.2) !important;
            color: #E8C979 !important;
        }
        .features-section .feature-card h4 {
            color: #FFFFFF !important;
            font-size: 1.35rem;
        }
        .features-section .feature-card p {
            color: rgba(255,255,255,0.76) !important;
            line-height: 1.7;
        }
        .beneficios-section {
            min-height: calc(100vh - 80px);
            background:
                linear-gradient(115deg, rgba(28,25,27,0.94), rgba(28,25,27,0.78)),
                url('img/Fondo.png') center center / cover no-repeat !important;
        }
        .beneficios-section h2 {
            color: #FFFFFF !important;
            font-size: clamp(2rem, 3.5vw, 3rem);
            letter-spacing: -0.04em;
        }
        .beneficios-section .beneficio-card {
            min-height: 290px;
            padding: 28px !important;
            border: 1px solid rgba(185,151,78,0.32) !important;
            border-radius: 18px !important;
            background: rgba(35,31,34,0.84) !important;
            box-shadow: 0 16px 36px rgba(0,0,0,0.2) !important;
            backdrop-filter: blur(10px);
            transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
        }
        .beneficios-section .beneficio-card:hover {
            transform: translateY(-5px);
            border-color: rgba(232,201,121,0.72) !important;
            box-shadow: 0 20px 42px rgba(0,0,0,0.3) !important;
        }
        .beneficios-section .beneficio-icon {
            width: 58px;
            height: 58px;
            border: 1px solid rgba(232,201,121,0.34);
            border-radius: 15px;
            background: rgba(168,95,109,0.2);
            color: #E8C979;
            box-shadow: none;
        }
        .beneficios-section .beneficio-card h5 {
            color: #FFFFFF !important;
        }
        .beneficios-section .beneficio-card p {
            color: rgba(255,255,255,0.76) !important;
            line-height: 1.7;
        }
        @media (max-width: 991px) {
            .navbar-home .navbar-collapse {
                margin-top: 12px;
                padding: 12px;
                border: 1px solid rgba(255,255,255,0.12);
                border-radius: 10px;
                background: rgba(28,25,27,0.97);
            }
            .hero-section {
                min-height: 760px !important;
                padding-top: 130px !important;
            }
            .hero-section .hero-copy {
                max-width: 700px !important;
                padding: 40px 0 !important;
                text-align: left !important;
            }
            .hero-capabilities {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 24px 0;
                margin-top: 36px;
            }
            .hero-capability:nth-child(3) {
                padding-left: 0;
                border-left: 0;
            }
        }
        @media (max-width: 576px) {
            .navbar-home .navbar-logo {
                height: 58px;
                width: 74px;
            }
            .hero-section {
                min-height: 100vh !important;
                padding-top: 112px !important;
                padding-bottom: 28px !important;
            }
            .hero-section > .container {
                align-items: flex-start;
                padding-top: 24px !important;
                padding-bottom: 24px !important;
            }
            .hero-section::before {
                background-position: 57% center !important;
            }
            .hero-section::after {
                background: linear-gradient(90deg, rgba(28,25,27,0.58), rgba(28,25,27,0.32)) !important;
            }
            .hero-section .hero-copy {
                padding: 32px 0 !important;
            }
            .hero-section .hero-copy h1,
            .hero-section .hero-copy h1 span {
                font-size: clamp(2.55rem, 12vw, 3.6rem) !important;
            }
            .hero-section .hero-actions {
                align-items: stretch;
                flex-direction: column;
            }
            .hero-section .hero-actions .btn {
                width: 100%;
            }
            .hero-capabilities {
                gap: 20px 0;
                margin-top: 30px;
                padding-top: 16px;
            }
            .hero-capability {
                padding: 0 12px;
            }
            .hero-capability strong {
                font-size: 0.87rem;
            }
            .hero-capability span {
                font-size: 0.75rem;
            }
            .hero-capability:nth-child(odd) {
                padding-left: 0;
                border-left: 0;
            }
        }
        .bella-chat {
            position: fixed;
            right: 24px;
            bottom: 24px;
            z-index: 1080;
            width: min(370px, calc(100vw - 32px));
            height: min(500px, calc(100dvh - 40px));
            min-height: min(390px, calc(100dvh - 40px));
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid rgba(185,151,78,0.55);
            border-radius: 16px;
            background: #FFFFFF;
            box-shadow: 0 22px 60px rgba(28,25,27,0.24);
            font-family: 'Inter', sans-serif;
        }
        .bella-chat[hidden] {
            display: none;
        }
        .bella-chat__header {
            display: flex;
            align-items: center;
            gap: 11px;
            min-height: 68px;
            padding: 11px 14px;
            color: #FFFFFF;
            background: linear-gradient(120deg, #282226, #6F4652);
            border-bottom: 2px solid #B9974E;
        }
        .bella-chat__avatar {
            display: grid;
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            place-items: center;
            border: 2px solid rgba(255,255,255,0.88);
            border-radius: 50%;
            background: #FFFFFF;
            color: #A85F6D;
            font-size: 16px;
        }
        .bella-chat__identity {
            min-width: 0;
            flex: 1;
        }
        .bella-chat__identity strong {
            display: block;
            font-size: 0.94rem;
            line-height: 1.2;
        }
        .bella-chat__identity span {
            color: rgba(255,255,255,0.74);
            font-size: 0.72rem;
        }
        .bella-chat__control {
            display: grid;
            width: 36px;
            height: 36px;
            place-items: center;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #FFFFFF;
            font-size: 1.05rem;
        }
        .bella-chat__control:hover {
            background: rgba(255,255,255,0.14);
        }
        .bella-chat__messages {
            display: flex;
            flex: 1;
            flex-direction: column;
            gap: 9px;
            overflow-y: auto;
            padding: 18px 15px;
            background: #FFFFFF;
        }
        .bella-chat__message {
            max-width: 86%;
            padding: 10px 13px;
            border-radius: 14px;
            color: #302A2D;
            background: #F4F0F1;
            font-size: 0.84rem;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }
        .bella-chat__message--bot {
            align-self: flex-start;
            border-bottom-left-radius: 5px;
        }
        .bella-chat__message--user {
            align-self: flex-end;
            border-bottom-right-radius: 5px;
            background: #A85F6D;
            color: #FFFFFF;
        }
        .bella-chat__composer {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0 13px 13px;
            padding: 5px 7px 5px 14px;
            border: 1px solid #CFC7CA;
            border-radius: 999px;
            background: #FFFFFF;
        }
        .bella-chat__composer:focus-within {
            border-color: #A85F6D;
            box-shadow: 0 0 0 3px rgba(168,95,109,0.13);
        }
        .bella-chat__input {
            min-width: 0;
            flex: 1;
            padding: 8px 0;
            border: 0;
            outline: 0;
            color: #292528;
            background: transparent;
            font: inherit;
            font-size: 0.82rem;
        }
        .bella-chat__input::placeholder {
            color: #777075;
        }
        .bella-chat__send {
            display: grid;
            width: 36px;
            height: 36px;
            flex: 0 0 36px;
            place-items: center;
            border: 0;
            border-radius: 50%;
            background: #A85F6D;
            color: #FFFFFF;
        }
        .bella-chat__send:hover {
            background: #854653;
        }
        .bella-chat-launcher {
            position: fixed;
            right: 24px;
            bottom: 24px;
            z-index: 1079;
            display: grid;
            width: 58px;
            height: 58px;
            place-items: center;
            border: 2px solid rgba(255,255,255,0.9);
            border-radius: 50%;
            background: #A85F6D;
            color: #FFFFFF;
            box-shadow: 0 8px 26px rgba(28,25,27,0.25);
            font-size: 1.35rem;
        }
        .bella-chat-launcher:hover {
            background: #854653;
            transform: translateY(-2px);
        }
        .bella-chat-launcher[hidden] {
            display: none;
        }
        @media (max-width: 576px) {
            .bella-chat {
                right: 8px;
                bottom: 10px;
                width: min(370px, calc(100vw - 48px));
                height: min(520px, calc(100dvh - 20px));
                min-height: min(340px, calc(100dvh - 20px));
            }
            .bella-chat-launcher {
                right: 16px;
                bottom: 16px;
            }
        }
    </style>
</head>
<body>

    <!-- ══════════════════════════════════════════
         NAVBAR
    ══════════════════════════════════════════ -->
    <nav class="navbar navbar-expand-lg fixed-top shadow-sm <?= in_array($seccion, ['inicio', 'caracteristicas', 'beneficios'], true) ? 'navbar-home' : '' ?>">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php?seccion=inicio">
                 <img src="img/logoprincipal-transparente.png?v=20261008-2" alt="Bella Tela" class="navbar-logo">
            </a>
            <button class="navbar-toggler" type="button"
                    data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item">
                        <a class="nav-link px-3" href="index.php?seccion=caracteristicas">Características</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="index.php?seccion=beneficios">Beneficios</a>
                    </li>
                    <?php if ($seccion !== 'inicio'): ?>
                    <li class="nav-item">
                        <a class="nav-link px-3 fw-semibold" href="index.php?seccion=inicio">
                            <i class="fa-solid fa-arrow-left me-1"></i>Volver al inicio
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item ms-lg-3 mt-3 mt-lg-0">
                        <a class="btn btn-primary px-4 py-2 fw-semibold"
                           href="../views/Auth/login.php">Iniciar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ══════════════════════════════════════════
         HERO SECTION
    ══════════════════════════════════════════ -->
    <?php if ($seccion === 'inicio'): ?>
    <section class="hero-section d-flex align-items-center">
        <div class="container py-5">
            <div class="row align-items-center justify-content-center">
                <div class="col-xl-8 col-lg-10 hero-copy text-lg-start text-center">
                    <span class="hero-eyebrow">GESTIÓN INTEGRAL PARA ALMACENES DE TELAS</span>
                    <h1 class="fw-bold mb-4">
                        <span class="line">Tu almacén de telas,</span>
                        <span class="line text-primary-custom">bajo control.</span>
                    </h1>
                    <p class="mb-4 pb-2 mx-auto mx-lg-0">
                        Administra inventario por metraje, registra ventas en el punto de venta
                        y consulta reportes desde una sola plataforma diseñada para tu negocio textil.
                    </p>
                </div>
            </div>
        </div>
    </section>
    <section class="home-capabilities-section py-5" aria-label="Funciones principales de SIGIBET">
        <div class="container py-3">
            <div class="hero-capabilities">
                        <div class="hero-capability">
                            <i class="hero-capability__icon fa-solid fa-boxes-stacked" aria-hidden="true"></i>
                            <strong>Inventario por metraje</strong>
                            <span>Controla existencias, referencias y niveles de stock de tus telas.</span>
                        </div>
                        <div class="hero-capability">
                            <i class="hero-capability__icon fa-solid fa-cash-register" aria-hidden="true"></i>
                            <strong>Ventas ágiles</strong>
                            <span>Registra productos y operaciones desde el punto de venta.</span>
                        </div>
                        <div class="hero-capability">
                            <i class="hero-capability__icon fa-solid fa-chart-line" aria-hidden="true"></i>
                            <strong>Reportes del negocio</strong>
                            <span>Consulta ventas y movimientos para tomar mejores decisiones.</span>
                        </div>
                        <div class="hero-capability">
                            <i class="hero-capability__icon fa-solid fa-user-shield" aria-hidden="true"></i>
                            <strong>Accesos por rol</strong>
                            <span>Organiza el acceso de administradores y empleados.</span>
                        </div>
            </div>
        </div>
    </section>
    <section class="catalog-section" aria-labelledby="catalogHeading">
        <div class="container">
            <div class="mb-4 mb-lg-5">
                <span class="catalog-eyebrow">CATÁLOGO BELLA TELA</span>
                <h2 class="catalog-heading mt-2 mb-3" id="catalogHeading">Telas para cada idea</h2>
                <p class="catalog-intro mb-0">Conoce las telas y productos de nuestro almacén.</p>
            </div>
            <?php if ($errorCatalogo): ?>
                <div class="alert alert-warning" role="alert">
                    En este momento no se pudo cargar el catálogo. Inténtalo de nuevo más tarde.
                </div>
            <?php elseif ($productosCatalogo): ?>
                <div class="row g-4">
                    <?php foreach ($productosCatalogo as $productoCatalogo): ?>
                        <?php
                        $nombreCatalogo = htmlspecialchars($productoCatalogo['nombre'] ?? 'Tela', ENT_QUOTES, 'UTF-8');
                        $imagenCatalogo = trim((string)($productoCatalogo['imagen'] ?? ''));
                        $rutaImagenCatalogo = $imagenCatalogo !== ''
                            ? 'img/' . ltrim($imagenCatalogo, '/\\')
                            : '';
                        ?>
                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <article class="catalog-card">
                                <div class="catalog-image-wrap">
                                    <?php if ($rutaImagenCatalogo !== ''): ?>
                                        <img class="catalog-image"
                                             src="<?php echo htmlspecialchars($rutaImagenCatalogo, ENT_QUOTES, 'UTF-8'); ?>"
                                             alt="<?php echo $nombreCatalogo; ?>"
                                             loading="lazy">
                                    <?php else: ?>
                                        <i class="catalog-image-placeholder fa-solid fa-swatchbook" aria-hidden="true"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="catalog-card-body">
                                    <?php if (!empty($productoCatalogo['categoria'])): ?>
                                        <span class="catalog-category"><?php echo htmlspecialchars($productoCatalogo['categoria'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php endif; ?>
                                    <h3 class="catalog-product-name"><?php echo $nombreCatalogo; ?></h3>
                                    <?php if (!empty($productoCatalogo['color'])): ?>
                                        <p class="catalog-product-color mb-2">
                                            <i class="fa-solid fa-palette me-1" aria-hidden="true"></i>
                                            <?php echo htmlspecialchars($productoCatalogo['color'], ENT_QUOTES, 'UTF-8'); ?>
                                        </p>
                                    <?php endif; ?>
                                    <p class="catalog-product-description">
                                        <?php
                                        $descripcionCatalogo = trim((string)($productoCatalogo['descripcion'] ?? ''));
                                        echo htmlspecialchars(
                                            $descripcionCatalogo !== '' ? $descripcionCatalogo : 'Consulta en nuestro almacén para conocer más detalles de esta tela.',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                        ?>
                                    </p>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="catalog-empty text-center">
                    <i class="fa-solid fa-swatchbook mb-2" aria-hidden="true"></i>
                    <p class="mb-0">Pronto encontrarás aquí las telas de nuestro catálogo.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════
         CARACTERÍSTICAS  (id=caracteristicas)
    ══════════════════════════════════════════ -->
    <?php if ($seccion === 'caracteristicas'): ?>
    <section id="caracteristicas" class="features-section py-5">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold mb-3" style="color:#1C191B;">
                    Diseñado específicamente para el sector textil
                </h2>
                    <p class="w-75 mx-auto" style="color:#6B4A40;">
                    Herramientas potentes y fáciles de usar para optimizar las operaciones
                    diarias de tu almacén de telas.
                </p>
            </div>
            <div class="row g-4">
                <!-- Feature 1 -->
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 border-0 shadow-sm p-4">
                        <div class="feature-icon bg-primary-light text-primary-custom mb-4 rounded-3
                                    d-inline-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-warehouse fa-lg"></i>
                        </div>
                        <h4 class="fw-bold mb-3" style="color:#1C191B;">Gestión de Inventario</h4>
                        <p style="color:#6B4A40;" class="mb-0">
                            Control exacto de metrajes, colores y tipos de tela.
                            Alertas de stock bajo y reportes detallados en tiempo real.
                        </p>
                    </div>
                </div>
                <!-- Feature 2 -->
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 border-0 shadow-sm p-4">
                        <div class="feature-icon bg-primary-light text-primary-custom mb-4 rounded-3
                                    d-inline-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-receipt fa-lg"></i>
                        </div>
                        <h4 class="fw-bold mb-3" style="color:#1C191B;">Facturación POS</h4>
                        <p style="color:#6B4A40;" class="mb-0">
                            Sistema de punto de venta ágil y eficiente.
                            Generación de facturas de forma rápida para reducir tiempos de espera en caja.
                        </p>
                    </div>
                </div>
                <!-- Feature 3 -->
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100 border-0 shadow-sm p-4">
                        <div class="feature-icon bg-primary-light text-primary-custom mb-4 rounded-3
                                    d-inline-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-chart-line fa-lg"></i>
                        </div>
                        <h4 class="fw-bold mb-3" style="color:#1C191B;">Reportes Estratégicos</h4>
                        <p style="color:#6B4A40;" class="mb-0">
                            Visualiza el rendimiento de tu negocio. Análisis de ventas,
                            productos más vendidos y métricas clave para la toma de decisiones.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════
         BENEFICIOS  (id=beneficios)  ← SECCIÓN NUEVA
    ══════════════════════════════════════════ -->
    <?php if ($seccion === 'beneficios'): ?>
    <section id="beneficios" class="beneficios-section py-5">
        <div class="container py-5">
            <div class="text-center mb-4">
                <h2 class="fw-bold text-white mb-2">¿Por qué elegir Bella Tela?</h2>
                <p style="color:rgba(255,255,255,0.82);" class="w-75 mx-auto mb-0">
                    Una plataforma creada para resolver los retos reales de tu almacén de telas:
                    control, rapidez, organización y trazabilidad en cada proceso.
                </p>
            </div>
            <div class="row g-3">

                <!-- Beneficio 1 -->
                <div class="col-md-6 col-lg-4">
                    <div class="beneficio-card">
                        <div class="beneficio-header">
                            <div class="beneficio-icon">
                                <i class="fa-solid fa-bolt"></i>
                            </div>
                        </div>
                        <h5>Rapidez en caja</h5>
                        <p>
                            Gestiona ventas, descuentos y cobros con rapidez. El sistema busca referencias por nombre,
                            color o código y reduce el tiempo de atención en cada venta para mejorar la experiencia del cliente.
                        </p>
                    </div>
                </div>

                <!-- Beneficio 2 -->
                <div class="col-md-6 col-lg-4">
                    <div class="beneficio-card">
                        <div class="beneficio-header">
                            <div class="beneficio-icon">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                        </div>
                        <h5>Control por roles</h5>
                        <p>
                            Define permisos para administrador y empleados. Así mantienes el control del inventario,
                            las ventas y los reportes según el perfil de cada usuario y evitas accesos no autorizados.
                        </p>
                    </div>
                </div>

                <!-- Beneficio 3 -->
                <div class="col-md-6 col-lg-4">
                    <div class="beneficio-card">
                        <div class="beneficio-header">
                            <div class="beneficio-icon">
                                <i class="fa-solid fa-bell"></i>
                            </div>
                        </div>
                        <h5>Alertas inteligentes</h5>
                        <p>
                            Recibe avisos automáticos cuando una tela o referencia se acerca a su stock mínimo.
                            Esto te ayuda a reabastecer a tiempo y evitar pérdidas por falta de producto.
                        </p>
                    </div>
                </div>

                <!-- Beneficio 4 -->
                <div class="col-md-6 col-lg-4">
                    <div class="beneficio-card">
                        <div class="beneficio-header">
                            <div class="beneficio-icon">
                                <i class="fa-solid fa-chart-pie"></i>
                            </div>
                        </div>
                        <h5>Reportes en tiempo real</h5>
                        <p>
                            Analiza ventas, productos más vendidos, inventario disponible y movimientos del almacén.
                            Todo en un solo lugar para decisiones rápidas y mejor gestionadas.
                        </p>
                    </div>
                </div>

                <!-- Beneficio 5 -->
                <div class="col-md-6 col-lg-4">
                    <div class="beneficio-card">
                        <div class="beneficio-header">
                            <div class="beneficio-icon">
                                <i class="fa-solid fa-floppy-disk"></i>
                            </div>
                        </div>
                        <h5>Backup y seguridad</h5>
                        <p>
                            Guarda copias de seguridad de la base de datos y restaura información cuando lo necesites.
                            Así proteges tus datos y mantienes la continuidad del negocio ante cualquier incidencia.
                        </p>
                    </div>
                </div>

                <!-- Beneficio 6 -->
                <div class="col-md-6 col-lg-4">
                    <div class="beneficio-card">
                        <div class="beneficio-header">
                            <div class="beneficio-icon">
                                <i class="fa-solid fa-palette"></i>
                            </div>
                        </div>
                        <h5>Personalización total</h5>
                        <p>
                            Configura la información de tu empresa, impuestos, tipo de facturación y apariencia del sistema.
                            Bella Tela se adapta a tu marca y a la forma en que operas cada día.
                        </p>
                    </div>
                </div>

            </div>

        </div>
    </section>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════
         FOOTER
    ══════════════════════════════════════════ -->
    <footer style="background:#36211E;" class="text-white py-4">
        <div class="container text-center">
            <p class="mb-0" style="color:rgba(255,255,255,0.6);">
                &copy; <?php echo date('Y'); ?> Bella Tela &mdash; SIGIBET.
                Todos los derechos reservados.
            </p>
        </div>
    </footer>

    <section class="bella-chat" id="bellaChat" aria-label="Chat de ayuda de Bella Tela" hidden>
        <header class="bella-chat__header">
            <div class="bella-chat__avatar" aria-hidden="true"><i class="fa-solid fa-comments"></i></div>
            <div class="bella-chat__identity">
                <strong>Asistente Bella Tela</strong>
                <span>Ayuda de SIGIBET</span>
            </div>
            <button class="bella-chat__control" id="bellaChatClose" type="button" aria-label="Cerrar chat">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </header>
        <div class="bella-chat__messages" id="bellaChatMessages" role="log" aria-live="polite">
            <div class="bella-chat__message bella-chat__message--bot">¡Hola! 👋 Nos alegra tenerte por aquí.</div>
            <div class="bella-chat__message bella-chat__message--bot">¿Cómo te llamas?</div>
        </div>
        <form class="bella-chat__composer" id="bellaChatForm">
            <input class="bella-chat__input" id="bellaChatInput" type="text" maxlength="300"
                   placeholder="Pregúntame sobre SIGIBET..." autocomplete="off" aria-label="Escribe tu mensaje">
            <button class="bella-chat__send" type="submit" aria-label="Enviar mensaje">
                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
            </button>
        </form>
    </section>
    <button class="bella-chat-launcher" id="bellaChatLauncher" type="button"
            aria-label="Abrir chat de ayuda" aria-controls="bellaChat" aria-expanded="false">
        <i class="fa-solid fa-comments" aria-hidden="true"></i>
    </button>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (() => {
            const chat = document.getElementById('bellaChat');
            const launcher = document.getElementById('bellaChatLauncher');
            const closeButton = document.getElementById('bellaChatClose');
            const form = document.getElementById('bellaChatForm');
            const input = document.getElementById('bellaChatInput');
            const messages = document.getElementById('bellaChatMessages');
            let visitorName = '';

            const addMessage = (text, sender) => {
                const message = document.createElement('div');
                message.className = `bella-chat__message bella-chat__message--${sender}`;
                message.textContent = text;
                messages.appendChild(message);
                messages.scrollTop = messages.scrollHeight;
            };

            const isLegalQuestion = (question) => {
                const normalized = question.toLocaleLowerCase('es')
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '');
                return /\b(legal\w*|juridic\w*|ley(?:es)?|abogad\w*|demanda\w*|contrato\w*|asesoria\w*|legislacion|derechos|tribunal\w*|denuncia\w*|litigio\w*|clausula\w*|normativa\w*|regulacion\w*)\b/.test(normalized);
            };

            const getReply = (question) => {
                const normalized = question.toLocaleLowerCase('es');
                if (isLegalQuestion(question)) {
                    return 'No estoy autorizado para brindar asesoría legal ni interpretar términos o documentos jurídicos. Para recibir orientación confiable, consulta a un profesional del derecho.';
                }
                if (/inventario|stock|existencia|tela|metraje|producto/.test(normalized)) {
                    return 'SIGIBET te ayuda a gestionar productos textiles, controlar existencias por metraje y detectar niveles bajos de inventario.';
                }
                if (/venta|factura|caja|pos|cobro/.test(normalized)) {
                    return 'El módulo de Punto de Venta permite registrar ventas, agregar productos y consultar el historial de tus operaciones.';
                }
                if (/reporte|informe|estad[ií]stica/.test(normalized)) {
                    return 'En Reportes puedes revisar el comportamiento de las ventas y obtener información útil para tomar decisiones.';
                }
                if (/entrar|ingresar|acceso|iniciar|contrase[nñ]a|login/.test(normalized)) {
                    return 'Para ingresar, usa el botón “Iniciar Sesión” de la página y escribe las credenciales que te asignó el administrador.';
                }
                if (/cliente|usuario|empleado|rol/.test(normalized)) {
                    return 'SIGIBET incluye gestión de clientes y usuarios con acceso de administrador o empleado, según los permisos asignados.';
                }
                return 'Puedo orientarte sobre inventario, ventas, reportes, clientes o acceso al sistema. ¿Qué te gustaría consultar?';
            };

            closeButton.addEventListener('click', () => {
                chat.hidden = true;
                launcher.hidden = false;
                launcher.setAttribute('aria-expanded', 'false');
                launcher.focus();
            });

            launcher.addEventListener('click', () => {
                chat.hidden = false;
                launcher.hidden = true;
                launcher.setAttribute('aria-expanded', 'true');
                input.focus();
            });

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                const text = input.value.trim();
                if (!text) return;

                addMessage(text, 'user');
                input.value = '';

                if (isLegalQuestion(text)) {
                    addMessage(getReply(text), 'bot');
                    return;
                }

                if (!visitorName) {
                    visitorName = text.split(/\s+/)[0];
                    addMessage(`¡Mucho gusto, ${visitorName}! Puedo ayudarte con SIGIBET. ¿Qué deseas saber?`, 'bot');
                    return;
                }

                addMessage(getReply(text), 'bot');
            });
        })();
    </script>
</body>
</html>
