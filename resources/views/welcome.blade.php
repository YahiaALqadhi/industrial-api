<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ \App\Models\Setting::getValue('site_name', 'NINGBO PASAFEITE') }}</title>
    <meta name="description" content="Professional industrial solutions platform by NINGBO PASAFEITE.">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #004D80;
            --soft: #BDBDBD2B;
            --text: #0f172a;
            --muted: #64748b;
            --white: #ffffff;
            --border: #e2e8f0;
            --bg: #f8fafc;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
            color: var(--text);
        }

        .container {
            width: min(1200px, 92%);
            margin: 0 auto;
        }

        .navbar {
            padding: 20px 0;
            border-bottom: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.95);
            position: sticky;
            top: 0;
            backdrop-filter: blur(10px);
            z-index: 50;
        }

        .navbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
            color: var(--text);
        }

        .brand img {
            height: 56px;
            width: auto;
            object-fit: contain;
            background: #fff;
            border-radius: 12px;
            padding: 6px;
            border: 1px solid var(--border);
        }

        .brand-title {
            font-size: 22px;
            font-weight: 800;
            line-height: 1.1;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
            border-radius: 14px;
            padding: 12px 18px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            opacity: 0.92;
            transform: translateY(-1px);
        }

        .btn-soft {
            background: var(--soft);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .btn-soft:hover {
            background: #eef4f8;
        }

        .hero {
            padding: 72px 0 48px;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 32px;
            align-items: center;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            background: var(--soft);
            color: var(--primary);
            border: 1px solid #dbeafe;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 18px;
        }

        .hero h1 {
            font-size: clamp(36px, 6vw, 64px);
            line-height: 1.05;
            margin-bottom: 18px;
        }

        .hero p {
            font-size: 17px;
            line-height: 1.8;
            color: var(--muted);
            max-width: 700px;
            margin-bottom: 26px;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
        }

        .hero-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: 28px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
        }

        .hero-card h3 {
            font-size: 18px;
            margin-bottom: 14px;
        }

        .hero-card ul {
            list-style: none;
            display: grid;
            gap: 12px;
        }

        .hero-card li {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 14px 16px;
            font-size: 14px;
            color: var(--muted);
        }

        .section {
            padding: 28px 0 70px;
        }

        .section-title {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 22px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        .card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 14px 35px rgba(15, 23, 42, 0.04);
        }

        .card-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: var(--soft);
            color: var(--primary);
            display: grid;
            place-items: center;
            font-size: 20px;
            margin-bottom: 16px;
        }

        .card h3 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .card p {
            color: var(--muted);
            line-height: 1.7;
            font-size: 14px;
        }

        .contact-box {
            margin-top: 26px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 24px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .contact-item {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 18px;
        }

        .contact-item strong {
            display: block;
            margin-bottom: 8px;
            color: var(--primary);
        }

        .contact-item span {
            color: var(--muted);
            line-height: 1.7;
            font-size: 14px;
        }

        .footer {
            padding: 28px 0 40px;
            border-top: 1px solid var(--border);
            color: var(--muted);
            font-size: 14px;
        }

        @media (max-width: 992px) {
            .hero-grid,
            .cards,
            .contact-box {
                grid-template-columns: 1fr;
            }

            .navbar-inner {
                flex-direction: column;
                align-items: flex-start;
            }

            .nav-actions {
                width: 100%;
                flex-wrap: wrap;
            }

            .brand-title {
                font-size: 18px;
            }

            .hero {
                padding-top: 48px;
            }
        }
    </style>
</head>
<body>
    @php
        $siteName = \App\Models\Setting::getValue('site_name', 'NINGBO PASAFEITE');
        $logo = \App\Models\Setting::getValue('logo');
        $email = \App\Models\Setting::getValue('email', 'Not set yet');
        $phone = \App\Models\Setting::getValue('phone', 'Not set yet');
        $address = \App\Models\Setting::getValue('address', 'Not set yet');
    @endphp

    <header class="navbar">
        <div class="container navbar-inner">
            <a href="/" class="brand">
                @if($logo)
                    <img src="{{ asset('storage/' . $logo) }}" alt="{{ $siteName }}">
                @endif
                <div class="brand-title">{{ $siteName }}</div>
            </a>

            <div class="nav-actions">
                <a href="{{ url('/admin') }}" class="btn btn-primary">Admin Login</a>
                <a href="{{ url('/api/products') }}" class="btn btn-soft">API Products</a>
            </div>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container hero-grid">
                <div>
                    <div class="badge">Industrial Solutions Platform</div>
                    <h1>Professional industrial commerce backend, ready for modern applications.</h1>
                    <p>
                        {{ $siteName }} provides a modern management platform for industrial products,
                        categories, orders, customers, support conversations, and business operations
                        with a clean admin experience and scalable API architecture.
                    </p>

                    <div class="hero-actions">
                        <a href="{{ url('/admin') }}" class="btn btn-primary">Go to Admin Panel</a>
                        <a href="#features" class="btn btn-soft">Explore Features</a>
                    </div>
                </div>

                <div class="hero-card">
                    <h3>Platform Highlights</h3>
                    <ul>
                        <li>Product and category management with image galleries</li>
                        <li>Cart, checkout, orders, and address management</li>
                        <li>Integrated customer support chat with auto-reply</li>
                        <li>Notifications, statistics, and admin dashboard insights</li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="section" id="features">
            <div class="container">
                <h2 class="section-title">Core Features</h2>

                <div class="cards">
                    <div class="card">
                        <div class="card-icon">📦</div>
                        <h3>Product Management</h3>
                        <p>
                            Manage products, categories, stock, featured items, and multiple gallery images
                            through a professional admin panel.
                        </p>
                    </div>

                    <div class="card">
                        <div class="card-icon">🛒</div>
                        <h3>Orders & Checkout</h3>
                        <p>
                            Full backend support for cart operations, checkout flow, order creation,
                            order tracking, and customer shipping data.
                        </p>
                    </div>

                    <div class="card">
                        <div class="card-icon">💬</div>
                        <h3>Customer Support</h3>
                        <p>
                            Built-in chat system with automatic reply, conversation tracking,
                            unread counters, and admin-side response management.
                        </p>
                    </div>
                </div>

                <div class="contact-box">
                    <div class="contact-item">
                        <strong>Email</strong>
                        <span>{{ $email }}</span>
                    </div>

                    <div class="contact-item">
                        <strong>Phone</strong>
                        <span>{{ $phone }}</span>
                    </div>

                    <div class="contact-item">
                        <strong>Address</strong>
                        <span>{{ $address }}</span>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            © {{ now()->year }} {{ $siteName }}. All rights reserved.
        </div>
    </footer>
</body>
</html>