<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

$stats = DB::get()->query(
    'SELECT COUNT(*) AS total_orders, COALESCE(SUM(total_amount),0) AS total_revenue, COUNT(DISTINCT user_id) AS total_users
     FROM orders
     WHERE payment_status="paid"'
)->fetch();

$shopProducts = DB::get()->query("
    SELECT * FROM shop_products
    WHERE is_active = 1
    ORDER BY category, sort_order ASC
")->fetchAll();

$shopByCategory = [];
foreach ($shopProducts as $p) {
    $shopByCategory[$p['category']][] = $p;
}

$shopCategories = [
    'month'    => ['label' => '1 Month Boosts',  'icon' => '/assets/img/icons/1mboost.svg',   'w' => 20, 'h' => 20],
    'three'    => ['label' => '3 Months Boosts', 'icon' => '/assets/img/icons/3boostss.svg',  'w' => 25, 'h' => 25],
    'growth'   => ['label' => 'Growth Packs',    'icon' => '/assets/img/icons/growth.svg',    'w' => 20, 'h' => 20],
    'tokens'   => ['label' => 'Nitro Tokens',    'icon' => '/assets/img/icons/tokens.svg',    'w' => 15, 'h' => 15],
    'member'   => ['label' => 'Members',         'icon' => '/assets/img/icons/members.svg',   'w' => 15, 'h' => 15],
    'reaction' => ['label' => 'Reactions',       'icon' => '/assets/img/icons/reaction.svg',  'w' => 20, 'h' => 20],
    'accounts' => ['label' => 'Accounts',        'icon' => '/assets/img/icons/accounts.svg',  'w' => 20, 'h' => 20],
];

$firstShopCat = array_key_first($shopByCategory) ?? 'month';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>BoostLab — Premium Discord Server Boosting</title>
<meta name="description" content="Instant Discord server boosts, members, tokens and growth products. Fast delivery, secure checkout, and premium support.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

:root{
  --bg:#05070f;
  --bg2:#070a14;
  --surface:#0c1020;
  --surface2:#11162a;

  --border:#1c2545;
  --border2:#2a3568;

  --accent:#9333ea;
  --accent2:#6d28d9;
  --accent3:#a855f7;
  --accent-soft:#c084fc;
  --accent-pink:#e879f9;
  --accent-blue:#818cf8;
  --accent-glow:rgba(147,51,234,.30);

  --green:#10b981;
  --gold:#f59e0b;
  --red:#ef4444;

  --text:#f5f7ff;
  --muted:#aab0d4;
  --dim:#737aa8;

  --r:0px;
  --t:.28s cubic-bezier(.4,0,.2,1);
}

html{scroll-behavior:smooth;color-scheme:dark}
body{
  font-family:'Inter',sans-serif;
  background:var(--bg);
  color:var(--text);
  overflow-x:hidden;
  cursor:none;
  -webkit-font-smoothing:antialiased;
}
a{color:inherit;text-decoration:none}
button{font-family:inherit;cursor:none;border:none;background:none}
img{display:block;max-width:100%}
ul{list-style:none}
input,select,textarea{font-family:inherit}

::-webkit-scrollbar{width:6px}
::-webkit-scrollbar-track{background:var(--bg)}
::-webkit-scrollbar-thumb{background:var(--surface);border-radius:6px}

.cursor{pointer-events:none;position:fixed;z-index:9999;top:0;left:0}
.cur--dot{
  position:fixed;top:0;left:0;width:10px;height:10px;
  margin:-5px 0 0 -5px;will-change:transform;pointer-events:none;z-index:9999;
}
.curouter{
  position:fixed;top:0;left:0;width:36px;height:36px;
  margin:-18px 0 0 -18px;will-change:transform;pointer-events:none;z-index:9998;
  transition:width .22s,height .22s,margin .22s,opacity .22s;
}
.cholder,.cholder-o{position:absolute;inset:0}
.cblock1{
  position:absolute;width:7px;height:7px;background:var(--accent);
  transition:all .22s;
}
.cblock1.is--tl{top:0;left:0;clip-path:polygon(0 0,100% 0,100% 30%,30% 100%,0 100%)}
.cblock1.is--tr{top:0;right:0;clip-path:polygon(0 0,100% 0,100% 100%,70% 100%,0 30%)}
.cblock1.is--bl{bottom:0;left:0;clip-path:polygon(0 0,30% 0,100% 70%,100% 100%,0 100%)}
.cblock1.is--br{bottom:0;right:0;clip-path:polygon(70% 0,100% 0,100% 100%,0 100%,0 70%)}
.cur--dot .cblock1{width:5px;height:5px;background:#fff}
.curouter.is--open{width:52px;height:52px;margin:-26px 0 0 -26px;opacity:.6}

.preloader{
  position:fixed;inset:0;z-index:10000;background:var(--bg);
  display:flex;align-items:center;justify-content:center;flex-direction:column;gap:32px;
  transition:opacity .6s ease,visibility .6s ease;
}
.preloader.hidden{opacity:0;visibility:hidden}
.pre-logo{
  font-family:'Space Grotesk',sans-serif;
  font-size:30px;font-weight:800;letter-spacing:-.04em;
  opacity:0;animation:preLogoIn .8s .3s ease forwards;
}
.pre-logo span{color:var(--accent-soft)}
.pre-bar{width:180px;height:2px;background:var(--border2);border-radius:1px;overflow:hidden}
.pre-fill{
  height:100%;
  background:linear-gradient(90deg,var(--accent-pink),var(--accent),var(--accent-blue));
  animation:preLoad 1.4s .2s ease forwards;width:0;
}
@keyframes preLogoIn{to{opacity:1}}
@keyframes preLoad{to{width:100%}}

.hud{
  position:fixed;left:28px;top:50%;transform:translateY(-50%);
  z-index:500;display:flex;flex-direction:column;gap:18px;pointer-events:none;
}
.hud-link{pointer-events:auto;display:flex;align-items:center;gap:10px;opacity:.45;transition:opacity .3s}
.hud-link:hover,.hud-link.active{opacity:1}
.hud-dot{
  width:8px;height:8px;border-radius:50%;
  border:1.5px solid var(--accent);background:transparent;
  transition:background .3s,transform .3s;flex-shrink:0;
}
.hud-link.active .hud-dot{background:var(--accent);transform:scale(1.3)}
.hud-label{
  font-size:11px;font-weight:600;letter-spacing:.8px;text-transform:uppercase;
  color:var(--dim);white-space:nowrap;
  opacity:0;transform:translateX(-6px);
  transition:opacity .2s,transform .2s;pointer-events:none;
}
.hud-link:hover .hud-label,.hud-link.active .hud-label{opacity:1;transform:translateX(0)}
.hud-track{
  width:1px;height:28px;background:var(--border);margin:0 3.5px;
  position:relative;overflow:hidden;
}
.hud-track-fill{
  position:absolute;top:0;left:0;right:0;height:0;
  background:var(--accent);transition:height .4s ease;
}

.scroll-prog{
  position:fixed;right:28px;bottom:36px;z-index:500;
  width:44px;height:44px;
  clip-path:polygon(0 0,88% 0,100% 12%,100% 100%,12% 100%,0 88%);
  background:var(--surface);border:1px solid var(--border2);
  display:flex;align-items:center;justify-content:center;
  font-size:10px;font-weight:700;color:var(--dim);
}
.scroll-prog svg{position:absolute;inset:0;width:100%;height:100%}
.scroll-prog-circle{
  fill:none;stroke:var(--accent);stroke-width:2;
  stroke-linecap:round;transform:rotate(-90deg);transform-origin:50% 50%;
  stroke-dasharray:0 120;transition:stroke-dasharray .1s linear;
}
.scroll-txt{position:relative;z-index:1}

.nav{
  position:fixed;top:0;left:0;right:0;z-index:600;
  padding:0 24px;transition:background var(--t),backdrop-filter var(--t),border-color var(--t);
}
.nav.scrolled{
  background:rgba(7,9,15,.88);backdrop-filter:blur(20px);
  border-bottom:1px solid var(--border);
}
.nav-inner{
  max-width:1200px;margin:0 auto;height:72px;
  display:flex;align-items:center;justify-content:space-between;
}
.brand{
  font-family:'Space Grotesk',sans-serif;font-weight:800;font-size:20px;
  letter-spacing:-.04em;display:flex;align-items:center;gap:12px;
}
.brand span{color:var(--accent-soft)}
.brand-icon{
  width:36px;height:36px;
  display:flex;align-items:center;justify-content:center;
  clip-path:polygon(0 0,88% 0,100% 12%,100% 100%,12% 100%,0 88%);
  background:linear-gradient(135deg,rgba(232,121,249,.18),rgba(147,51,234,.24));
  border:1px solid rgba(192,132,252,.22);
  box-shadow:0 0 0 1px rgba(255,255,255,.03) inset;
}
.brand-icon svg{width:20px;height:20px;color:var(--accent-soft)}
.nav-right{display:flex;align-items:center;gap:20px}
.menu-btn{
  display:flex;flex-direction:column;gap:5px;padding:8px;
  border:1px solid var(--border);
  clip-path:polygon(0 0,88% 0,100% 12%,100% 100%,12% 100%,0 88%);
  transition:border-color .2s,background .2s;
}
.menu-btn:hover{
  border-color:rgba(192,132,252,.55);
  background:rgba(147,51,234,.06);
}
.menu-line{
  width:22px;height:1.5px;background:var(--text);
  transition:transform .35s,opacity .35s,width .35s;transform-origin:center;
}
.menu-btn.open .menu-line:nth-child(1){transform:translateY(6.5px) rotate(45deg)}
.menu-btn.open .menu-line:nth-child(2){opacity:0;width:0}
.menu-btn.open .menu-line:nth-child(3){transform:translateY(-6.5px) rotate(-45deg)}

.nav-menu{
  position:fixed;inset:0;z-index:590;background:var(--bg);
  display:flex;align-items:center;justify-content:center;
  opacity:0;pointer-events:none;transition:opacity .45s cubic-bezier(.4,0,.2,1);
}
.nav-menu.open{opacity:1;pointer-events:all}
.nav-menu-inner{
  display:grid;grid-template-columns:1fr 1fr;gap:0 80px;
  max-width:900px;width:100%;padding:0 40px;
}
.nav-link-item{overflow:hidden;border-bottom:1px solid var(--border)}
.nav-link{
  display:flex;align-items:center;gap:18px;padding:24px 0;
  font-family:'Space Grotesk',sans-serif;font-size:clamp(28px,4vw,48px);
  font-weight:700;letter-spacing:-.03em;color:var(--text);
  opacity:.3;transform:translateY(40px);transition:opacity .4s,color .3s;
}
.nav-menu.open .nav-link{transform:translateY(0)}
.nav-link:hover{opacity:1;color:var(--accent-soft)}
.nav-link-num{
  font-size:13px;font-weight:600;color:var(--muted);
  letter-spacing:.05em;min-width:28px;transition:color .3s;
}
.nav-link:hover .nav-link-num{color:var(--accent-soft)}
.nav-menu-side{
  border-left:1px solid var(--border);padding-left:40px;
  display:flex;flex-direction:column;justify-content:center;gap:32px;
}
.nav-menu-sub-title{
  font-size:11px;font-weight:600;letter-spacing:1.2px;
  text-transform:uppercase;color:var(--muted);margin-bottom:4px;
}
.nav-menu-cta{
  font-family:'Space Grotesk',sans-serif;font-size:14px;font-weight:600;
  color:var(--dim);line-height:1.6;max-width:230px;
}

.hero{
  position:relative;min-height:100vh;display:flex;align-items:center;
  overflow:hidden;padding-top:72px;
}
#hero-canvas{position:absolute;inset:0;width:100%;height:100%;pointer-events:none}
.hero-glow{
  position:absolute;inset:0;pointer-events:none;
  background:
    radial-gradient(ellipse 70% 60% at 50% -10%,rgba(147,51,234,.20) 0%,transparent 60%),
    radial-gradient(ellipse 40% 40% at 80% 70%,rgba(129,140,248,.14) 0%,transparent 50%);
}
.hero-inner{
  position:relative;z-index:2;max-width:1200px;margin:0 auto;
  padding:100px 80px 80px;text-align:center;width:100%;
}
.hero-eyebrow{
  display:inline-flex;align-items:center;gap:8px;
  background:rgba(147,51,234,.10);border:1px solid rgba(147,51,234,.25);
  padding:6px 16px;margin-bottom:30px;
  font-size:12px;font-weight:600;letter-spacing:1px;text-transform:uppercase;
  color:var(--accent-soft);
  clip-path:polygon(0 0,calc(100% - 8px) 0,100% 8px,100% 100%,8px 100%,0 calc(100% - 8px));
}
.hero-eyebrow-dot{
  width:6px;height:6px;background:var(--green);border-radius:50%;
  animation:blink 2.2s infinite;
}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.25}}
h1.hero-title{
  font-family:'Space Grotesk',sans-serif;
  font-size:clamp(42px,7vw,88px);font-weight:800;
  line-height:1.04;letter-spacing:-.04em;margin-bottom:24px;
}
.hero-title .line2{
  background:linear-gradient(135deg,var(--accent-pink) 0%,var(--accent3) 45%,var(--accent-blue) 100%);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.hero-sub{
  font-size:clamp(16px,2vw,20px);color:var(--dim);
  max-width:620px;margin:0 auto 44px;line-height:1.7;
}
.hero-actions{
  display:flex;align-items:center;justify-content:center;gap:16px;
  flex-wrap:wrap;margin-bottom:72px;
}
.btn-primary,.pkg-btn-accent,.btn-buy,.modal-submit{
  display:inline-flex;align-items:center;justify-content:center;gap:10px;
  background:linear-gradient(135deg,var(--accent),var(--accent2));
  color:#fff;font-size:15px;font-weight:700;padding:16px 36px;
  clip-path:polygon(0 0,calc(100% - 14px) 0,100% 14px,100% 100%,14px 100%,0 calc(100% - 14px));
  transition:transform var(--t),box-shadow var(--t),opacity var(--t);
  box-shadow:0 0 36px var(--accent-glow);
}
.btn-primary:hover,.pkg-btn-accent:hover,.modal-submit:hover,.btn-buy:hover{
  transform:translateY(-2px);
  box-shadow:0 0 56px rgba(147,51,234,.42);
}
.btn-secondary{
  display:inline-flex;align-items:center;justify-content:center;gap:8px;
  background:rgba(255,255,255,.05);border:1px solid var(--border2);
  color:var(--text);font-size:15px;font-weight:500;padding:16px 32px;
  clip-path:polygon(0 0,calc(100% - 14px) 0,100% 14px,100% 100%,14px 100%,0 calc(100% - 14px));
  transition:background var(--t),border-color var(--t);
}
.btn-secondary:hover{background:rgba(255,255,255,.09);border-color:rgba(255,255,255,.25)}

.hero-stats{
  display:flex;justify-content:center;
  border:1px solid var(--border);
  background:rgba(255,255,255,.02);backdrop-filter:blur(10px);
  max-width:540px;margin:0 auto;overflow:hidden;
  clip-path:polygon(0 0,calc(100% - 16px) 0,100% 16px,100% 100%,16px 100%,0 calc(100% - 16px));
}
.stat-item{flex:1;padding:22px 16px;text-align:center;border-right:1px solid var(--border)}
.stat-item:last-child{border-right:none}
.stat-val{
  font-family:'Space Grotesk',sans-serif;font-size:26px;font-weight:800;line-height:1;
}
.stat-lbl{
  font-size:10px;font-weight:600;text-transform:uppercase;
  letter-spacing:.9px;color:var(--muted);margin-top:5px;
}

section{padding:110px 0}
.container{max-width:1200px;margin:0 auto;padding:0 80px}
.tc{text-align:center}
.sec-tag{
  display:inline-flex;align-items:center;gap:7px;
  font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;
  color:var(--accent-soft);margin-bottom:14px;
}
.sec-tag::before{content:'';width:20px;height:1px;background:var(--accent-soft)}
.sec-title{
  font-family:'Space Grotesk',sans-serif;
  font-size:clamp(28px,4vw,46px);font-weight:800;
  letter-spacing:-.03em;line-height:1.12;margin-bottom:16px;
}
.sec-sub{font-size:16px;color:var(--dim);max-width:620px;line-height:1.7}
.tc .sec-sub{margin:0 auto}

.features{background:var(--bg)}
.feat-grid{
  display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
  gap:1px;margin-top:60px;background:var(--border);
  clip-path:polygon(0 0,calc(100% - 24px) 0,100% 24px,100% 100%,24px 100%,0 calc(100% - 24px));
}
.feat-card{
  background:var(--bg);padding:36px 30px;
  transition:background var(--t);position:relative;overflow:hidden;
}
.feat-card::before{
  content:'';position:absolute;top:0;left:0;right:0;height:2px;
  background:linear-gradient(90deg,transparent,var(--accent-soft),transparent);
  transform:scaleX(0);transition:transform .4s;
}
.feat-card:hover{background:var(--surface)}
.feat-card:hover::before{transform:scaleX(1)}
.feat-icon{
  width:52px;height:52px;display:flex;align-items:center;justify-content:center;
  font-size:24px;margin-bottom:20px;
  clip-path:polygon(0 0,calc(100% - 10px) 0,100% 10px,100% 100%,10px 100%,0 calc(100% - 10px));
}
.feat-icon.purple{background:rgba(147,51,234,.16)}
.feat-icon.green{background:rgba(16,185,129,.12)}
.feat-icon.gold{background:rgba(245,158,11,.12)}
.feat-icon.blue{background:rgba(129,140,248,.14)}
.feat-card h3{font-size:16px;font-weight:700;margin-bottom:9px}
.feat-card p{font-size:14px;color:var(--dim);line-height:1.7}

.shop{background:var(--bg)}
.cat-tabs{display:flex;flex-wrap:wrap;gap:10px;margin:40px 0 32px}
.cat-tab{
  display:inline-flex;align-items:center;gap:8px;
  padding:10px 18px;font-size:13px;font-weight:700;
  background:var(--surface);border:1.5px solid var(--border);
  color:var(--muted);
  clip-path:polygon(0 0,calc(100% - 10px) 0,100% 10px,100% 100%,10px 100%,0 calc(100% - 10px));
  transition:all var(--t);
}
.cat-tab:hover{border-color:var(--border2);color:var(--text)}
.cat-tab.active{
  border-color:var(--accent);color:var(--accent-soft);
  background:rgba(147,51,234,.08);
}
.cat-icon{flex-shrink:0;opacity:.85}

.product-search-wrap{margin-bottom:28px}
.product-search{
  position:relative;
  width:100%;
  max-width:420px;
}
.product-search input{
  width:100%;
  height:52px;
  padding:0 20px 0 52px;
  border-radius:0;
  background:var(--surface);
  border:1px solid var(--border);
  color:var(--text);
  font-size:.95rem;
  clip-path:polygon(0 0,calc(100% - 10px) 0,100% 10px,100% 100%,10px 100%,0 calc(100% - 10px));
  transition:border-color .25s,box-shadow .25s,background .25s;
}
.product-search input::placeholder{color:var(--dim)}
.product-search input:focus{
  outline:none;
  border-color:rgba(147,51,234,.6);
  background:var(--surface2);
  box-shadow:0 0 0 3px rgba(147,51,234,.15),0 8px 30px rgba(147,51,234,.15);
}
.product-search svg{
  position:absolute;top:50%;left:18px;transform:translateY(-50%);
  width:18px;height:18px;color:var(--accent-soft);pointer-events:none;opacity:.85;
}

.member-switch{display:flex;gap:10px;margin-bottom:24px;flex-wrap:wrap}
.products-grid{
  display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));
  gap:20px;margin-top:8px;
}
.product-card{
  background:var(--surface);border:1px solid var(--border);
  display:flex;flex-direction:column;position:relative;
  clip-path:polygon(0 0,calc(100% - 18px) 0,100% 18px,100% 100%,18px 100%,0 calc(100% - 18px));
  transition:border-color var(--t),transform var(--t),box-shadow var(--t);
}
.product-card:hover{
  border-color:rgba(147,51,234,.5);
  transform:translateY(-4px);
  box-shadow:0 20px 50px rgba(0,0,0,.4);
}
.product-badge{
  position:absolute;top:-1px;right:20px;
  background:linear-gradient(135deg,var(--accent),var(--accent2));
  color:#fff;font-size:10px;font-weight:800;letter-spacing:.8px;
  text-transform:uppercase;padding:3px 12px;
  clip-path:polygon(0 0,100% 0,100% 100%,8px 100%,0 calc(100% - 7px));
}
.product-img-wrap{
  width:100%;aspect-ratio:16/9;background:var(--bg2);
  display:flex;align-items:center;justify-content:center;
  overflow:hidden;border-bottom:1px solid var(--border);
}
.product-img-wrap img{width:100%;height:100%;object-fit:cover}
.product-img-placeholder{font-size:48px}
.product-body{
  padding:20px 18px;display:flex;flex-direction:column;flex:1;gap:10px;
}
.product-name{
  font-family:'Space Grotesk',sans-serif;font-size:16px;
  font-weight:800;letter-spacing:-.02em;color:var(--text);
}
.product-prices{display:flex;align-items:center;gap:8px}
.product-price-new{
  font-family:'Space Grotesk',sans-serif;font-size:22px;
  font-weight:800;color:var(--accent-soft);
}
.product-price-old{
  font-size:14px;color:var(--dim);text-decoration:line-through;
}
.product-features{
  display:flex;flex-direction:column;gap:5px;padding:0;margin:0;flex:1;
}
.product-features li{
  display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--dim);
}
.product-features li::before{
  content:'✓';width:16px;height:16px;flex-shrink:0;
  background:rgba(16,185,129,.12);color:var(--green);
  display:flex;align-items:center;justify-content:center;
  font-size:10px;font-weight:800;
  clip-path:polygon(0 0,calc(100% - 4px) 0,100% 4px,100% 100%,4px 100%,0 calc(100% - 4px));
}
.btn-buy{
  width:100%;margin-top:auto;padding:12px;font-size:13px;
}
.btn-buy:disabled{
  background:var(--surface);color:var(--dim);
  border:1px solid var(--border);box-shadow:none;cursor:not-allowed;
}
.no-products-msg{
  grid-column:1/-1;text-align:center;
  padding:60px 20px;color:var(--dim);font-size:15px;
}

.how{background:var(--bg)}
.steps{
  display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
  gap:0;margin-top:60px;position:relative;background:var(--border);
  clip-path:polygon(0 0,calc(100% - 24px) 0,100% 24px,100% 100%,24px 100%,0 calc(100% - 24px));
}
.step{
  background:var(--bg);padding:40px 30px;text-align:center;
  border-right:1px solid var(--border);transition:background var(--t);
}
.step:last-child{border-right:none}
.step:hover{background:var(--surface)}
.step-num{
  width:60px;height:60px;background:var(--surface);border:1px solid var(--border2);
  display:flex;align-items:center;justify-content:center;
  margin:0 auto 22px;
  font-family:'Space Grotesk',sans-serif;font-size:20px;font-weight:800;color:var(--accent-soft);
  clip-path:polygon(0 0,calc(100% - 12px) 0,100% 12px,100% 100%,12px 100%,0 calc(100% - 12px));
}
.step h3{font-size:16px;font-weight:700;margin-bottom:10px}
.step p{font-size:14px;color:var(--dim);line-height:1.7}

.testi{background:var(--bg2)}
.testi-grid{
  display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
  gap:20px;margin-top:60px;
}
.testi-card{
  background:var(--surface);border:1px solid var(--border);padding:26px;
  clip-path:polygon(0 0,calc(100% - 16px) 0,100% 16px,100% 100%,16px 100%,0 calc(100% - 16px));
  transition:border-color var(--t),transform var(--t);
}
.testi-card:hover{border-color:rgba(147,51,234,.35);transform:translateY(-3px)}
.stars{color:var(--gold);font-size:14px;margin-bottom:14px;letter-spacing:2px}
.testi-txt{font-size:14px;color:var(--dim);line-height:1.75;margin-bottom:20px}
.testi-author{display:flex;align-items:center;gap:12px}
.testi-av{
  width:38px;height:38px;font-weight:800;font-size:14px;color:#fff;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
  clip-path:polygon(0 0,calc(100% - 8px) 0,100% 8px,100% 100%,8px 100%,0 calc(100% - 8px));
}
.av1{background:linear-gradient(135deg,#9333ea,#6d28d9)}
.av2{background:linear-gradient(135deg,#e879f9,#9333ea)}
.av3{background:linear-gradient(135deg,#818cf8,#9333ea)}
.testi-name{font-size:13px;font-weight:700}
.testi-handle{font-size:12px;color:var(--muted)}

.cta-sec{background:var(--bg)}
.cta-box{
  border:1px solid rgba(147,51,234,.25);
  background:linear-gradient(135deg,rgba(147,51,234,.10),rgba(129,140,248,.08));
  padding:72px 48px;text-align:center;position:relative;overflow:hidden;
  clip-path:polygon(0 0,calc(100% - 28px) 0,100% 28px,100% 100%,28px 100%,0 calc(100% - 28px));
}
.cta-box::before{
  content:'';position:absolute;top:-60%;left:50%;transform:translateX(-50%);
  width:600px;height:300px;
  background:radial-gradient(ellipse,rgba(147,51,234,.18) 0%,transparent 70%);
  pointer-events:none;
}
.cta-box h2{
  font-family:'Space Grotesk',sans-serif;
  font-size:clamp(26px,4vw,44px);font-weight:800;letter-spacing:-.03em;
  margin-bottom:14px;
}
.cta-box p{font-size:17px;color:var(--dim);margin:0 auto 36px;max-width:620px;line-height:1.7}

.footer{background:var(--bg2);border-top:1px solid var(--border);padding:52px 0 30px}
.footer-inner{
  display:flex;justify-content:space-between;flex-wrap:wrap;gap:36px;
  margin-bottom:40px;
}
.footer-brand p{
  font-size:13px;color:var(--muted);max-width:260px;margin-top:10px;line-height:1.7;
}
.footer-col h4{
  font-size:11px;font-weight:700;letter-spacing:1px;
  text-transform:uppercase;color:var(--muted);margin-bottom:16px;
}
.footer-col ul{display:flex;flex-direction:column;gap:10px}
.footer-col a{font-size:13px;color:var(--dim);transition:color .2s}
.footer-col a:hover{color:var(--accent-soft)}
.footer-bottom{
  border-top:1px solid var(--border);padding-top:24px;
  display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px;
  font-size:12px;color:var(--muted);
}

.modal-overlay{
  position:fixed;inset:0;z-index:800;background:rgba(0,0,0,.78);
  backdrop-filter:blur(10px);display:flex;align-items:center;justify-content:center;
  padding:20px;opacity:0;pointer-events:none;transition:opacity var(--t);
}
.modal-overlay.active{opacity:1;pointer-events:all}
.modal{
  background:var(--bg2);border:1px solid var(--border2);width:100%;max-width:520px;
  max-height:92vh;overflow-y:auto;position:relative;
  clip-path:polygon(0 0,calc(100% - 24px) 0,100% 24px,100% 100%,24px 100%,0 calc(100% - 24px));
  transform:translateY(16px) scale(.98);transition:transform var(--t);
}
.modal-overlay.active .modal{transform:translateY(0) scale(1)}
.modal-hdr{
  padding:26px 30px 22px;border-bottom:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between;
  position:sticky;top:0;background:var(--bg2);z-index:2;
}
.modal-title{font-family:'Space Grotesk',sans-serif;font-size:18px;font-weight:800}
.modal-close{
  width:34px;height:34px;background:rgba(255,255,255,.06);
  border:1px solid var(--border);color:var(--dim);
  display:flex;align-items:center;justify-content:center;font-size:18px;
  clip-path:polygon(0 0,calc(100% - 8px) 0,100% 8px,100% 100%,8px 100%,0 calc(100% - 8px));
  transition:background .2s,color .2s;
}
.modal-close:hover{background:rgba(255,255,255,.1);color:var(--text)}
.modal-body{padding:26px 30px}
.modal-pkg-info{
  background:rgba(147,51,234,.08);border:1px solid rgba(147,51,234,.22);
  padding:14px 18px;margin-bottom:24px;
  display:flex;justify-content:space-between;align-items:center;
  clip-path:polygon(0 0,calc(100% - 10px) 0,100% 10px,100% 100%,10px 100%,0 calc(100% - 10px));
}
.modal-pkg-name{font-weight:700;font-size:15px}
.modal-pkg-price{
  font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:800;color:var(--accent-soft);
}
.modal-err{
  background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);
  color:#fca5a5;padding:10px 14px;font-size:13px;margin-bottom:16px;display:none;
  clip-path:polygon(0 0,calc(100% - 8px) 0,100% 8px,100% 100%,8px 100%,0 calc(100% - 8px));
}
.form-grp{margin-bottom:18px}
.form-lbl{
  display:block;font-size:11px;font-weight:700;
  letter-spacing:.7px;text-transform:uppercase;color:var(--muted);margin-bottom:8px;
}
.form-inp,.form-sel{
  width:100%;padding:12px 14px;background:var(--surface);border:1.5px solid var(--border);
  color:var(--text);font-size:14px;outline:none;
  clip-path:polygon(0 0,calc(100% - 10px) 0,100% 10px,100% 100%,10px 100%,0 calc(100% - 10px));
  transition:border-color var(--t),box-shadow var(--t),background var(--t);
}
.form-inp:focus,.form-sel:focus{
  border-color:var(--accent);box-shadow:0 0 0 3px rgba(147,51,234,.12);background:var(--surface2);
}
.form-inp::placeholder{color:var(--muted)}
.form-sel option{background:var(--bg2)}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.form-hint{font-size:11px;color:var(--muted);margin-top:5px}
.pay-tabs{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:22px}
.pay-tab{
  padding:14px 10px;text-align:center;background:var(--surface);
  border:1.5px solid var(--border);transition:all var(--t);
  clip-path:polygon(0 0,calc(100% - 10px) 0,100% 10px,100% 100%,10px 100%,0 calc(100% - 10px));
}
.pay-tab:hover{border-color:var(--border2)}
.pay-tab.active{border-color:var(--accent);background:rgba(147,51,234,.10)}
.pay-tab-icon{font-size:22px;margin-bottom:6px}
.pay-tab-label{font-size:12px;font-weight:700;color:var(--dim);display:block}
.pay-tab.active .pay-tab-label{color:var(--accent-soft)}
.crypto-wrap{display:none}
.crypto-wrap.on{display:block}
.loyalty-row{
  display:flex;align-items:center;justify-content:space-between;
  background:var(--surface);border:1px solid var(--border);
  padding:12px 14px;margin-bottom:18px;
  clip-path:polygon(0 0,calc(100% - 10px) 0,100% 10px,100% 100%,10px 100%,0 calc(100% - 10px));
}
.loyalty-lbl{font-weight:700;font-size:13px}
.loyalty-pts{font-size:12px;color:var(--muted);margin-top:3px}
.toggle{
  width:42px;height:24px;background:var(--border);border-radius:100px;
  position:relative;flex-shrink:0;transition:background .25s;
}
.toggle::after{
  content:'';position:absolute;width:18px;height:18px;background:#fff;border-radius:50%;
  top:3px;left:3px;transition:transform .25s;
}
.toggle.on{background:var(--accent)}
.toggle.on::after{transform:translateX(18px)}
.qty-wrap{
  display:flex;align-items:center;background:var(--surface);border:1.5px solid var(--border);
  clip-path:polygon(0 0,calc(100% - 10px) 0,100% 10px,100% 100%,10px 100%,0 calc(100% - 10px));
  overflow:hidden;
}
.qty-btn{
  width:42px;height:44px;color:var(--text);font-size:20px;display:flex;align-items:center;justify-content:center;
  transition:background .2s;
}
.qty-btn:hover{background:rgba(255,255,255,.07)}
.qty-inp{
  flex:1;border:none;background:transparent;text-align:center;
  font-size:15px;font-weight:700;color:var(--text);outline:none;padding:0;
}
.price-prev{
  background:var(--surface);border:1px solid var(--border);
  padding:14px 16px;margin-bottom:20px;
  clip-path:polygon(0 0,calc(100% - 10px) 0,100% 10px,100% 100%,10px 100%,0 calc(100% - 10px));
}
.price-row{
  display:flex;justify-content:space-between;font-size:13px;padding:3px 0;color:var(--dim);
}
.price-row.total{
  border-top:1px solid var(--border);margin-top:8px;padding-top:10px;
  font-size:16px;font-weight:800;color:var(--text);font-family:'Space Grotesk',sans-serif;
}
.price-row .grn{color:var(--green)}
.modal-submit{width:100%;padding:14px;font-size:15px;font-weight:800}
.modal-submit:disabled{opacity:.5;pointer-events:none}

.reveal{
  opacity:0;transform:translateY(28px);filter:blur(4px);
  transition:opacity .75s ease,transform .75s ease,filter .75s ease;
}
.reveal.in{opacity:1;transform:translateY(0);filter:blur(0)}

@keyframes spin{to{transform:rotate(360deg)}}

@media (max-width:900px){
  .container{padding:0 24px}
  .hero-inner{padding:80px 24px 60px}
  .nav-menu-inner{grid-template-columns:1fr}
  .nav-menu-side{display:none}
  .hud{display:none}
}
@media (max-width:640px){
  .feat-grid,.steps{grid-template-columns:1fr}
  .step,.feat-card{border-right:none}
  .step{border-bottom:1px solid var(--border)}
  .hero-stats{flex-direction:column}
  .stat-item{border-right:none;border-bottom:1px solid var(--border)}
  .stat-item:last-child{border-bottom:none}
  .form-row{grid-template-columns:1fr}
  .products-grid{grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px}
  .product-price-new{font-size:18px}
}
@media (hover:none),(max-width:900px){
  body,button,.cat-tab,.btn-buy{cursor:auto}
  .cur--dot,.curouter{display:none}
}
</style>
</head>
<body>

<div class="preloader" id="preloader">
  <div class="pre-logo">Boost<span>Lab</span></div>
  <div class="pre-bar"><div class="pre-fill"></div></div>
</div>

<div class="cur--dot" id="curDot">
  <div class="cholder">
    <div class="cblock1 is--tl"></div>
    <div class="cblock1 is--bl"></div>
    <div class="cblock1 is--tr"></div>
    <div class="cblock1 is--br"></div>
  </div>
</div>
<div class="curouter" id="curOuter">
  <div class="cholder-o">
    <div class="cblock1 is--tl"></div>
    <div class="cblock1 is--bl"></div>
    <div class="cblock1 is--tr"></div>
    <div class="cblock1 is--br"></div>
  </div>
</div>

<nav class="hud" id="hud">
  <a href="#hero" class="hud-link active" data-sec="hero"><span class="hud-label">Intro</span><div class="hud-dot"></div></a>
  <div class="hud-track"><div class="hud-track-fill"></div></div>
  <a href="#features" class="hud-link" data-sec="features"><span class="hud-label">Why Us</span><div class="hud-dot"></div></a>
  <div class="hud-track"><div class="hud-track-fill"></div></div>
  <a href="#shop" class="hud-link" data-sec="shop"><span class="hud-label">Shop</span><div class="hud-dot"></div></a>
  <div class="hud-track"><div class="hud-track-fill"></div></div>
  <a href="#how-it-works" class="hud-link" data-sec="how-it-works"><span class="hud-label">Process</span><div class="hud-dot"></div></a>
  <div class="hud-track"><div class="hud-track-fill"></div></div>
  <a href="#reviews" class="hud-link" data-sec="reviews"><span class="hud-label">Reviews</span><div class="hud-dot"></div></a>
</nav>

<div class="scroll-prog" id="scrollProg">
  <svg viewBox="0 0 44 44">
    <circle class="scroll-prog-circle" id="scrollCircle" cx="22" cy="22" r="19"></circle>
  </svg>
  <span class="scroll-txt" id="scrollPct">0%</span>
</div>

<nav class="nav" id="navbar">
  <div class="nav-inner">
    <a href="#hero" class="brand">
      <div class="brand-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
          <path d="M13 2L4 14h7l-1 8 10-12h-7l0-8z"></path>
        </svg>
      </div>
      Boost<span>Lab</span>
    </a>
    <div class="nav-right">
      <button class="menu-btn" id="menuBtn" aria-label="Menu">
        <div class="menu-line"></div>
        <div class="menu-line"></div>
        <div class="menu-line"></div>
      </button>
    </div>
  </div>
</nav>

<div class="nav-menu" id="navMenu">
  <div class="nav-menu-inner">
    <div class="nav-links-col">
      <div class="nav-link-item"><a href="#hero" class="nav-link"><span class="nav-link-num">01</span>Home</a></div>
      <div class="nav-link-item"><a href="#shop" class="nav-link"><span class="nav-link-num">02</span>Shop</a></div>
      <div class="nav-link-item"><a href="#how-it-works" class="nav-link"><span class="nav-link-num">03</span>How it works</a></div>
      <div class="nav-link-item"><a href="#reviews" class="nav-link"><span class="nav-link-num">04</span>Reviews</a></div>
    </div>
    <div class="nav-menu-side">
      <div>
        <div class="nav-menu-sub-title">Get Boosts</div>
        <div class="nav-menu-cta">Instant Discord server boosts and growth products with fast delivery and premium support.</div>
      </div>
      <button class="btn-primary" onclick="closeMenu();scrollToPackages()">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path></svg>
        Order Now
      </button>
    </div>
  </div>
</div>

<section class="hero" id="hero">
  <canvas id="hero-canvas"></canvas>
  <div class="hero-glow"></div>
  <div class="hero-inner">
    <div class="hero-eyebrow reveal">
      <span class="hero-eyebrow-dot"></span>
      Trusted by <?= number_format(max(500, (int)$stats['total_users'] + 480)) ?> server owners
    </div>

    <h1 class="hero-title reveal">
      Boost Your Server<br>
      <span class="line2">With BoostLab</span>
    </h1>

    <p class="hero-sub reveal">
      Instant Discord server boosts, members, reactions, tokens and growth products. Secure checkout, premium delivery speed, and a clean experience from order to completion.
    </p>

    <div class="hero-actions reveal">
      <button class="btn-primary" onclick="scrollToPackages()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path></svg>
        Get Boosts Now
      </button>
      <a href="#how-it-works" class="btn-secondary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polygon points="10 8 16 12 10 16 10 8"></polygon></svg>
        How it works
      </a>
    </div>

    <div class="hero-stats reveal">
      <div class="stat-item">
        <div class="stat-val" id="stat-orders" data-target="<?= max(1000, (int)$stats['total_orders'] + 950) ?>">0</div>
        <div class="stat-lbl">Orders Done</div>
      </div>
      <div class="stat-item">
        <div class="stat-val" id="stat-users" data-target="<?= max(500, (int)$stats['total_users'] + 480) ?>">0</div>
        <div class="stat-lbl">Happy Clients</div>
      </div>
      <div class="stat-item">
        <div class="stat-val">5.0</div>
        <div class="stat-lbl">Avg. Rating</div>
      </div>
    </div>
  </div>
</section>

<section class="features" id="features">
  <div class="container">
    <div class="tc">
      <div class="sec-tag reveal">Why BoostLab</div>
      <h2 class="sec-title reveal">Everything you need<br>to level up fast</h2>
      <p class="sec-sub reveal">A single storefront for server growth with secure checkout, fast delivery, and products designed for Discord communities.</p>
    </div>

    <div class="feat-grid">
      <div class="feat-card reveal">
        <div class="feat-icon purple">⚡</div>
        <h3>Instant Delivery</h3>
        <p>Boosts and growth products are processed quickly after payment confirmation with a streamlined checkout flow.</p>
      </div>
      <div class="feat-card reveal">
        <div class="feat-icon green">🛡</div>
        <h3>Secure &amp; Safe</h3>
        <p>Orders are handled through verified systems with a focus on reliability, clean delivery, and stable service quality.</p>
      </div>
      <div class="feat-card reveal">
        <div class="feat-icon gold">💳</div>
        <h3>Flexible Payments</h3>
        <p>Support for card and crypto payments gives customers a simple way to choose the method that fits them best.</p>
      </div>
      <div class="feat-card reveal">
        <div class="feat-icon blue">🎁</div>
        <h3>Loyalty Rewards</h3>
        <p>Built-in loyalty logic keeps repeat customers engaged and creates a better long-term experience for server owners.</p>
      </div>
    </div>
  </div>
</section>

<?php if (!empty($shopProducts)): ?>
<section class="shop" id="shop">
  <div class="container">
    <div class="tc">
      <div class="sec-tag reveal">Shop</div>
      <h2 class="sec-title reveal">Our Products</h2>
      <p class="sec-sub reveal">
        Everything you need to grow your Discord server — boosts, members, reactions, tokens and more.
      </p>
    </div>

    <div class="cat-tabs" id="shopCatTabs">
      <?php $first = true; foreach ($shopCategories as $cat => $info): ?>
        <?php if (empty($shopByCategory[$cat])) continue; ?>
        <button class="cat-tab <?= $first ? 'active' : '' ?>" data-cat="<?= $cat ?>" onclick="showCategory('<?= $cat ?>', this)">
          <img src="<?= htmlspecialchars($info['icon']) ?>" class="cat-icon" width="<?= (int)$info['w'] ?>" height="<?= (int)$info['h'] ?>" alt="">
          <?= htmlspecialchars($info['label']) ?>
        </button>
      <?php $first = false; endforeach; ?>
    </div>

    <div class="product-search-wrap">
      <div class="product-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input type="text" id="productSearch" placeholder="Search products..." oninput="filterProducts()">
      </div>
    </div>

    <div class="member-switch" id="memberSwitch" style="display:none">
      <button class="cat-tab active" onclick="filterMembers('offline', this)">Offline Members</button>
      <button class="cat-tab" onclick="filterMembers('online', this)">Online Members</button>
    </div>

    <div class="products-grid" id="productsGrid">
      <?php foreach ($shopProducts as $p): ?>
        <?php
          $feats = $p['features'] ? (json_decode($p['features'], true) ?? []) : [];
          $visible = ($p['category'] === $firstShopCat);
        ?>
        <div class="product-card reveal"
             data-category="<?= htmlspecialchars($p['category']) ?>"
             <?= $p['member_type'] ? 'data-member="' . htmlspecialchars($p['member_type']) . '"' : '' ?>
             data-name="<?= htmlspecialchars(strtolower($p['name'])) ?>"
             style="display:<?= $visible ? 'flex' : 'none' ?>">

          <?php if (!empty($p['is_popular'])): ?>
            <div class="product-badge">Popular</div>
          <?php endif; ?>

          <div class="product-img-wrap">
            <?php if (!empty($p['image_path'])): ?>
              <img src="<?= htmlspecialchars($p['image_path']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
            <?php else: ?>
              <div class="product-img-placeholder">🚀</div>
            <?php endif; ?>
          </div>

          <div class="product-body">
            <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>

            <div class="product-prices">
              <span class="product-price-new">$<?= number_format((float)$p['price'], 2) ?></span>
              <?php if (!empty($p['old_price'])): ?>
                <span class="product-price-old">$<?= number_format((float)$p['old_price'], 2) ?></span>
              <?php endif; ?>
            </div>

            <?php if ($feats): ?>
              <ul class="product-features">
                <?php foreach ($feats as $f): ?>
                  <li><?= htmlspecialchars($f) ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>

            <button class="btn-buy" onclick="openProductModal(
              <?= (int)$p['id'] ?>,
              '<?= htmlspecialchars(addslashes($p['name'])) ?>',
              <?= number_format((float)$p['price'], 2, '.', '') ?>
            )">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <path d="M16 10a4 4 0 01-8 0"></path>
              </svg>
              <?= htmlspecialchars($p['button_text'] ?: 'Buy Now') ?>
            </button>
          </div>
        </div>
      <?php endforeach; ?>

      <div class="no-products-msg" id="noProductsMsg" style="display:none">
        No products found. Try a different search.
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="how" id="how-it-works">
  <div class="container">
    <div class="tc">
      <div class="sec-tag reveal">Process</div>
      <h2 class="sec-title reveal">Up and running in 4 steps</h2>
    </div>

    <div class="steps">
      <div class="step reveal">
        <div class="step-num">01</div>
        <h3>Choose a Product</h3>
        <p>Select the boost or growth product that fits your server and start the order flow.</p>
      </div>
      <div class="step reveal">
        <div class="step-num">02</div>
        <h3>Enter Server Details</h3>
        <p>Provide your Discord invite link, email, and optional server details for processing.</p>
      </div>
      <div class="step reveal">
        <div class="step-num">03</div>
        <h3>Pay Securely</h3>
        <p>Checkout with card or crypto using the payment option that works best for your audience.</p>
      </div>
      <div class="step reveal">
        <div class="step-num">04</div>
        <h3>Receive Delivery</h3>
        <p>Your order is processed and delivered fast so your community can grow without delay.</p>
      </div>
    </div>
  </div>
</section>

<section class="testi" id="reviews">
  <div class="container">
    <div class="tc">
      <div class="sec-tag reveal">Reviews</div>
      <h2 class="sec-title reveal">What our clients say</h2>
    </div>

    <div class="testi-grid">
      <div class="testi-card reveal">
        <div class="stars">★★★★★</div>
        <p class="testi-txt">Got my boosts within minutes after checkout. The flow was smooth and the site feels much cleaner than most Discord shops.</p>
        <div class="testi-author">
          <div class="testi-av av1">AX</div>
          <div>
            <div class="testi-name">AxelDev</div>
            <div class="testi-handle">axeldev · Discord</div>
          </div>
        </div>
      </div>

      <div class="testi-card reveal">
        <div class="stars">★★★★★</div>
        <p class="testi-txt">Paid with crypto and everything worked exactly as expected. Fast confirmation, fast delivery, and no confusing steps.</p>
        <div class="testi-author">
          <div class="testi-av av2">KR</div>
          <div>
            <div class="testi-name">KryptoRhino</div>
            <div class="testi-handle">kryptrhino · Discord</div>
          </div>
        </div>
      </div>

      <div class="testi-card reveal">
        <div class="stars">★★★★★</div>
        <p class="testi-txt">The new BoostLab look feels premium. Better branding, cleaner colors, and a much stronger visual identity overall.</p>
        <div class="testi-author">
          <div class="testi-av av3">NV</div>
          <div>
            <div class="testi-name">NightVault</div>
            <div class="testi-handle">nightvault · Discord</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="cta-sec">
  <div class="container">
    <div class="cta-box reveal">
      <h2>Ready to grow with BoostLab?</h2>
      <p>Join thousands of server owners who trust BoostLab for fast delivery, reliable products, and a premium storefront experience.</p>
      <button class="btn-primary" style="margin:0 auto" onclick="scrollToPackages()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path></svg>
        Get Started
      </button>
    </div>
  </div>
</section>

<footer class="footer">
  <div class="container">
    <div class="footer-inner">
      <div class="footer-brand">
        <div class="brand">
          <div class="brand-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <path d="M13 2L4 14h7l-1 8 10-12h-7l0-8z"></path>
            </svg>
          </div>
          Boost<span>Lab</span>
        </div>
        <p>Premium Discord server boosting and growth service. Fast, clean, and built for modern communities.</p>
      </div>

      <div class="footer-col">
        <h4>Services</h4>
        <ul>
          <li><a href="#shop">Shop</a></li>
          <li><a href="#how-it-works">How it works</a></li>
          <li><a href="#reviews">Reviews</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Support</h4>
        <ul>
          <li><a href="#">Discord Server</a></li>
          <li><a href="#">Track Order</a></li>
          <li><a href="#">Contact Us</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Legal</h4>
        <ul>
          <li><a href="#">Terms of Service</a></li>
          <li><a href="#">Privacy Policy</a></li>
          <li><a href="#">Refund Policy</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <span>&copy; <?= date('Y') ?> BoostLab. All rights reserved.</span>
      <span>Made for server owners worldwide</span>
    </div>
  </div>
</footer>

<div class="modal-overlay" id="orderModal" onclick="handleOverlayClick(event)">
  <div class="modal">
    <div class="modal-hdr">
      <div class="modal-title">Place Your Order</div>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>

    <div class="modal-body">
      <div class="modal-pkg-info">
        <div class="modal-pkg-name" id="modal-pkg-name"></div>
        <div class="modal-pkg-price" id="modal-pkg-price">$0.00</div>
      </div>

      <div class="modal-err" id="modal-err"></div>

      <div class="form-grp">
        <label class="form-lbl">Payment Method</label>
        <div class="pay-tabs">
          <div class="pay-tab active" id="tab-moneymotion" onclick="selectPay('moneymotion')">
            <div class="pay-tab-icon">💳</div>
            <span class="pay-tab-label">Card / Bank</span>
          </div>
          <div class="pay-tab" id="tab-nowpayments" onclick="selectPay('nowpayments')">
            <div class="pay-tab-icon">₿</div>
            <span class="pay-tab-label">Crypto</span>
          </div>
        </div>
      </div>

      <div class="crypto-wrap" id="crypto-wrap">
        <div class="form-grp">
          <label class="form-lbl">Select Cryptocurrency</label>
          <select class="form-sel" id="crypto-coin">
            <option value="usdtbsc">USDT (BEP-20)</option>
            <option value="usdttrc20">USDT (TRC-20)</option>
            <option value="btc">Bitcoin (BTC)</option>
            <option value="eth">Ethereum (ETH)</option>
            <option value="ltc">Litecoin (LTC)</option>
          </select>
        </div>
      </div>

      <div class="form-grp">
        <label class="form-lbl">Discord Server Invite Link <span style="color:#f87171">*</span></label>
        <input type="text" class="form-inp" id="serverlink" placeholder="https://discord.gg/yourserver">
        <div class="form-hint">Make sure the invite link doesn't expire</div>
      </div>

      <div class="form-grp">
        <label class="form-lbl">Server Name (optional)</label>
        <input type="text" class="form-inp" id="servername" placeholder="My Awesome Server">
      </div>

      <div class="form-row">
        <div class="form-grp">
          <label class="form-lbl">Email <span style="color:#f87171">*</span></label>
          <input type="email" class="form-inp" id="contactemail" placeholder="you@example.com">
        </div>
        <div class="form-grp">
          <label class="form-lbl">Discord Tag (optional)</label>
          <input type="text" class="form-inp" id="contactdiscord" placeholder="username#0000">
        </div>
      </div>

      <div class="form-grp">
        <label class="form-lbl">Quantity</label>
        <div class="qty-wrap">
          <button class="qty-btn" onclick="changeQty(-1)">−</button>
          <input type="number" class="qty-inp" id="quantity" value="1" min="1" max="10" oninput="updatePrice()">
          <button class="qty-btn" onclick="changeQty(1)">+</button>
        </div>
      </div>

      <div class="loyalty-row" id="loyalty-row" onclick="toggleLoyalty()" style="display:none">
        <div>
          <div class="loyalty-lbl">Use Loyalty Points</div>
          <div class="loyalty-pts" id="loyalty-pts-label">0 points available</div>
        </div>
        <div class="toggle" id="loyalty-toggle"></div>
      </div>

      <div class="price-prev" id="price-prev">
        <div class="price-row"><span>Package price</span><span id="pr-base">$0.00</span></div>
        <div class="price-row" id="pr-qty-row" style="display:none"><span>Quantity</span><span id="pr-qty">1</span></div>
        <div class="price-row grn" id="pr-disc-row" style="display:none"><span>Loyalty discount</span><span id="pr-disc" class="grn">−$0.00</span></div>
        <div class="price-row total"><span>Total</span><span id="pr-total">$0.00</span></div>
      </div>

      <button class="modal-submit" id="modal-submit-btn" onclick="submitOrder()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
        Proceed to Payment
      </button>
    </div>
  </div>
</div>

<script>
window.addEventListener('load', () => {
  setTimeout(() => document.getElementById('preloader').classList.add('hidden'), 1400);
});

const curDot = document.getElementById('curDot');
const curOuter = document.getElementById('curOuter');
let mx = 0, my = 0, ox = 0, oy = 0;

if (window.matchMedia('(hover:hover)').matches && window.innerWidth > 900) {
  document.addEventListener('mousemove', e => {
    mx = e.clientX;
    my = e.clientY;
    curDot.style.transform = `translate3d(${mx}px,${my}px,0)`;
  });

  (function lerpCursor() {
    ox += (mx - ox) * 0.1;
    oy += (my - oy) * 0.1;
    curOuter.style.transform = `translate3d(${ox}px,${oy}px,0)`;
    requestAnimationFrame(lerpCursor);
  })();

  document.querySelectorAll('a,button,.pkg-card,.feat-card,.testi-card,.pay-tab,.hud-link,.product-card,.cat-tab').forEach(el => {
    el.addEventListener('mouseenter', () => {
      curDot.querySelectorAll('.cblock1').forEach(b => b.style.background = '#fff');
      curOuter.classList.add('is--open');
    });
    el.addEventListener('mouseleave', () => {
      curDot.querySelectorAll('.cblock1').forEach(b => b.style.background = 'var(--accent)');
      curOuter.classList.remove('is--open');
    });
  });
}

(function () {
  const canvas = document.getElementById('hero-canvas');
  const ctx = canvas.getContext('2d');
  let W, H, particles = [];

  function resize() {
    W = canvas.width = canvas.offsetWidth;
    H = canvas.height = canvas.offsetHeight;
  }

  class Particle {
    constructor() { this.reset(); }
    reset() {
      this.x = Math.random() * W;
      this.y = Math.random() * H;
      this.r = Math.random() * 1.5 + .3;
      this.vx = (Math.random() - .5) * .35;
      this.vy = (Math.random() - .5) * .35;
      this.a = Math.random() * .45 + .08;
    }
    update() {
      this.x += this.vx;
      this.y += this.vy;
      if (this.x < 0 || this.x > W || this.y < 0 || this.y > H) this.reset();
    }
    draw() {
      ctx.beginPath();
      ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
      ctx.fillStyle = `rgba(192,132,252,${this.a})`;
      ctx.fill();
    }
  }

  function init() {
    resize();
    particles = Array.from({ length: 110 }, () => new Particle());
  }

  function drawLines() {
    for (let i = 0; i < particles.length; i++) {
      for (let j = i + 1; j < particles.length; j++) {
        const dx = particles[i].x - particles[j].x;
        const dy = particles[i].y - particles[j].y;
        const d = Math.sqrt(dx * dx + dy * dy);
        if (d < 100) {
          ctx.beginPath();
          ctx.strokeStyle = `rgba(147,51,234,${(1 - d / 100) * .14})`;
          ctx.lineWidth = .5;
          ctx.moveTo(particles[i].x, particles[i].y);
          ctx.lineTo(particles[j].x, particles[j].y);
          ctx.stroke();
        }
      }
    }
  }

  function loop() {
    ctx.clearRect(0, 0, W, H);
    particles.forEach(p => { p.update(); p.draw(); });
    drawLines();
    requestAnimationFrame(loop);
  }

  window.addEventListener('resize', resize);
  init();
  loop();
})();

const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
  navbar.classList.toggle('scrolled', window.scrollY > 40);
  updateScrollProgress();
  updateHUD();
});

function updateScrollProgress() {
  const max = document.body.scrollHeight - window.innerHeight;
  const pct = max > 0 ? window.scrollY / max : 0;
  const circ = document.getElementById('scrollCircle');
  const perim = 2 * Math.PI * 19;
  circ.style.strokeDasharray = `${pct * perim} ${perim}`;
  document.getElementById('scrollPct').textContent = `${Math.round(pct * 100)}%`;
}

const sections = ['hero', 'features', 'shop', 'how-it-works', 'reviews'];
function updateHUD() {
  let current = sections[0];
  sections.forEach(id => {
    const el = document.getElementById(id);
    if (el && el.getBoundingClientRect().top <= 160) current = id;
  });
  document.querySelectorAll('.hud-link').forEach(link => {
    link.classList.toggle('active', link.dataset.sec === current);
  });
}
updateScrollProgress();
updateHUD();

const menuBtn = document.getElementById('menuBtn');
const navMenu = document.getElementById('navMenu');
let menuOpen = false;

function openMenu() {
  menuOpen = true;
  menuBtn.classList.add('open');
  navMenu.classList.add('open');
  document.body.style.overflow = 'hidden';
  navMenu.querySelectorAll('.nav-link').forEach((l, i) => {
    setTimeout(() => {
      l.style.transform = 'translateY(0)';
      l.style.opacity = '1';
    }, i * 60);
  });
}

function closeMenu() {
  menuOpen = false;
  menuBtn.classList.remove('open');
  navMenu.classList.remove('open');
  document.body.style.overflow = '';
  navMenu.querySelectorAll('.nav-link').forEach(l => {
    l.style.opacity = '.3';
    l.style.transform = 'translateY(40px)';
  });
}

menuBtn.addEventListener('click', () => menuOpen ? closeMenu() : openMenu());
navMenu.querySelectorAll('.nav-link').forEach(link => link.addEventListener('click', closeMenu));

function animateCount(el, target) {
  let start = null;
  const dur = 1800;
  function step(ts) {
    if (!start) start = ts;
    const p = Math.min((ts - start) / dur, 1);
    const ease = 1 - Math.pow(1 - p, 3);
    el.textContent = Math.floor(ease * target).toLocaleString();
    if (p < 1) requestAnimationFrame(step);
  }
  requestAnimationFrame(step);
}

const obs = new IntersectionObserver(entries => {
  entries.forEach((entry, i) => {
    if (entry.isIntersecting) {
      setTimeout(() => {
        entry.target.classList.add('in');
        if (entry.target.id === 'stat-orders') animateCount(entry.target, parseInt(entry.target.dataset.target || '0', 10));
        if (entry.target.id === 'stat-users') animateCount(entry.target, parseInt(entry.target.dataset.target || '0', 10));
      }, i * 70);
      obs.unobserve(entry.target);
    }
  });
}, { threshold: .12 });

document.querySelectorAll('.reveal,#stat-orders,#stat-users').forEach(el => obs.observe(el));

function scrollToPackages() {
  const el = document.getElementById('shop');
  if (el) el.scrollIntoView({ behavior: 'smooth' });
}

let activePkgPrice = 0;
let activeProductId = null;
let activePayMethod = 'moneymotion';
let useLoyalty = false;
const loyaltyPts = 0;

function openProductModal(id, name, price) {
  activeProductId = id;
  activePkgPrice = parseFloat(price || 0);
  document.getElementById('modal-pkg-name').textContent = name;
  document.getElementById('quantity').value = 1;
  useLoyalty = false;
  document.getElementById('loyalty-toggle').classList.remove('on');
  clearModalErr();
  updatePrice();
  document.getElementById('orderModal').classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeModal() {
  document.getElementById('orderModal').classList.remove('active');
  document.body.style.overflow = '';
}

function handleOverlayClick(e) {
  if (e.target.id === 'orderModal') closeModal();
}

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    closeMenu();
    closeModal();
  }
});

function selectPay(method) {
  document.querySelectorAll('.pay-tab').forEach(t => t.classList.remove('active'));
  const tab = document.getElementById(`tab-${method}`);
  if (tab) tab.classList.add('active');
  activePayMethod = method;
  document.getElementById('crypto-wrap').classList.toggle('on', method === 'nowpayments');
}

function changeQty(d) {
  const inp = document.getElementById('quantity');
  inp.value = Math.max(1, Math.min(10, parseInt(inp.value || '1', 10) + d));
  updatePrice();
}

function toggleLoyalty() {
  if (!loyaltyPts) return;
  useLoyalty = !useLoyalty;
  document.getElementById('loyalty-toggle').classList.toggle('on', useLoyalty);
  updatePrice();
}

function updatePrice() {
  const qty = parseInt(document.getElementById('quantity').value || '1', 10);
  const sub = activePkgPrice * qty;
  const disc = useLoyalty ? Math.min(loyaltyPts / 100, sub * .5) : 0;
  const total = Math.max(0.50, sub - disc);

  document.getElementById('modal-pkg-price').textContent = `$${total.toFixed(2)}`;
  document.getElementById('pr-base').textContent = `$${activePkgPrice.toFixed(2)}`;
  document.getElementById('pr-total').textContent = `$${total.toFixed(2)}`;

  const qtyRow = document.getElementById('pr-qty-row');
  qtyRow.style.display = qty > 1 ? 'flex' : 'none';
  document.getElementById('pr-qty').textContent = qty;

  const discRow = document.getElementById('pr-disc-row');
  discRow.style.display = disc > 0 ? 'flex' : 'none';
  document.getElementById('pr-disc').textContent = `−$${disc.toFixed(2)}`;
}

function showModalErr(msg) {
  const el = document.getElementById('modal-err');
  el.textContent = msg;
  el.style.display = 'block';
  el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function clearModalErr() {
  const el = document.getElementById('modal-err');
  el.textContent = '';
  el.style.display = 'none';
}

function validDiscord(link) {
  return /^https?:\/\/(discord\.gg|discord\.com\/invite)\//i.test(link.trim());
}

function validEmail(email) {
  return /\S+@\S+\.\S+/.test(email.trim());
}

async function submitOrder() {
  clearModalErr();

  const serverlink = document.getElementById('serverlink').value.trim();
  const servername = document.getElementById('servername').value.trim();
  const contactemail = document.getElementById('contactemail').value.trim();
  const contactdiscord = document.getElementById('contactdiscord').value.trim();
  const quantity = parseInt(document.getElementById('quantity').value || '1', 10);
  const crypto = document.getElementById('crypto-coin')?.value || null;
  const btn = document.getElementById('modal-submit-btn');

  if (!serverlink) return showModalErr('Please enter your Discord server invite link.');
  if (!validDiscord(serverlink)) return showModalErr('Please enter a valid Discord invite link, e.g. https://discord.gg/xxxxx');
  if (!contactemail) return showModalErr('Please enter your email address.');
  if (!validEmail(contactemail)) return showModalErr('Please enter a valid email address.');

  btn.disabled = true;
  btn.innerHTML = '<div style="width:18px;height:18px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite"></div>Processing...';

  try {
    const payload = {
      paymentmethod: activePayMethod,
      productid: activeProductId,
      serverlink,
      servername: servername || null,
      email: contactemail,
      discordusername: contactdiscord || null,
      quantity,
      useloyalty: useLoyalty
    };

    if (activePayMethod === 'nowpayments' && crypto) {
      payload.cryptocurrency = crypto;
    }

    const res = await fetch('api/createorder.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const text = await res.text();
    let data;
    try {
      data = JSON.parse(text);
    } catch {
      throw new Error('Server returned an invalid response.');
    }

    if (data.redirect_url) {
      window.location.href = data.redirect_url;
      return;
    }

    showModalErr(data.error || 'An unexpected error occurred. Please try again.');
  } catch (e) {
    showModalErr(`Network error: ${e.message}`);
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>Proceed to Payment';
  }
}

let activeMemberFilter = 'offline';

function showCategory(cat, btn) {
  document.querySelectorAll('#shopCatTabs .cat-tab').forEach(t => t.classList.remove('active'));
  if (btn) btn.classList.add('active');

  const memberSwitch = document.getElementById('memberSwitch');
  memberSwitch.style.display = cat === 'member' ? 'flex' : 'none';

  if (cat === 'member') {
    activeMemberFilter = 'offline';
    memberSwitch.querySelectorAll('.cat-tab').forEach((t, i) => t.classList.toggle('active', i === 0));
  }

  document.querySelectorAll('#productsGrid .product-card').forEach(card => {
    const matchCat = card.dataset.category === cat;
    const matchMem = cat !== 'member' || card.dataset.member === activeMemberFilter;
    card.style.display = (matchCat && matchMem) ? 'flex' : 'none';
  });

  document.getElementById('productSearch').value = '';
  document.getElementById('noProductsMsg').style.display = 'none';
}

function filterMembers(type, btn) {
  activeMemberFilter = type;
  document.querySelectorAll('#memberSwitch .cat-tab').forEach(t => t.classList.remove('active'));
  if (btn) btn.classList.add('active');

  document.querySelectorAll('#productsGrid .product-card[data-category="member"]').forEach(card => {
    card.style.display = card.dataset.member === type ? 'flex' : 'none';
  });

  document.getElementById('noProductsMsg').style.display = 'none';
}

function filterProducts() {
  const q = document.getElementById('productSearch').value.toLowerCase().trim();
  let visible = 0;

  document.querySelectorAll('#productsGrid .product-card').forEach(card => {
    if (!q) {
      const activeBtn = document.querySelector('#shopCatTabs .cat-tab.active');
      const activeCat = activeBtn ? activeBtn.dataset.cat : null;
      if (activeCat) {
        const matchCat = card.dataset.category === activeCat;
        const matchMem = activeCat !== 'member' || card.dataset.member === activeMemberFilter;
        const show = matchCat && matchMem;
        card.style.display = show ? 'flex' : 'none';
        if (show) visible++;
      }
    } else {
      const show = (card.dataset.name || '').includes(q);
      card.style.display = show ? 'flex' : 'none';
      if (show) visible++;
    }
  });

  document.getElementById('noProductsMsg').style.display = (q && visible === 0) ? 'block' : 'none';
}
</script>

</body>
</html>
