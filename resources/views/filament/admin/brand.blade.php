@php
    $siteName = \App\Models\Setting::getValue('site_name', 'NINGBO PASAFEITE');
    $logo = \App\Models\Setting::getValue('logo');
@endphp

<style>
    :root {
        --pf-primary: #004D80;
        --pf-soft: #BDBDBD2B;
        --pf-border: #d7dde5;
        --pf-text: #0f172a;
        --pf-muted: #475569;
        --pf-card: #ffffff;
        --pf-page: #f3f6f9;
    }

    body {
        background: var(--pf-page) !important;
    }

    aside {
        border-right: 1px solid var(--pf-border) !important;
        background: #ffffff !important;
    }

    aside nav {
        padding-inline: 10px !important;
    }

    aside nav ul {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    aside nav ul li a,
    aside nav ul li button {
        border-radius: 14px !important;
        min-height: 46px;
        border: 1px solid transparent !important;
        transition: all 0.18s ease-in-out;
    }

    aside nav ul li a:hover,
    aside nav ul li button:hover {
        background: #eef6fb !important;
        border-color: #d7eaf5 !important;
        transform: translateX(2px);
    }

    aside nav ul li a span,
    aside nav ul li button span {
        font-weight: 600 !important;
        color: var(--pf-text) !important;
    }

    aside nav svg {
        color: var(--pf-primary) !important;
    }

    .fi-main {
        background: var(--pf-page) !important;
    }

    .fi-section,
    .fi-ta,
    .fi-fo-section,
    .fi-wi-widget {
        border: 1px solid var(--pf-border) !important;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.04) !important;
    }

    .custom-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 4px;
    }

    .custom-brand img {
        height: 48px;
        width: auto;
        object-fit: contain;
        flex-shrink: 0;
    }

    .custom-brand-title {
        font-weight: 900;
        font-size: 17px;
        color: var(--pf-text);
        white-space: nowrap;
        line-height: 1.2;
        letter-spacing: .2px;
    }

    @media (max-width: 1024px) {
        .custom-brand-title {
            font-size: 15px;
        }

        .custom-brand img {
            height: 42px;
        }
    }
</style>

<div class="custom-brand">
    @if($logo)
        <img src="{{ asset('storage/' . $logo) }}" alt="{{ $siteName }}">
    @endif

    <div class="custom-brand-title">
        {{ $siteName }}
    </div>
</div>