<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <title>أمان - غير متصل</title>
        <style>
        body{
            margin:0;
            background:#0d0d0d;
            color:#fff;
            font-family:sans-serif;
            display:flex;
            align-items:center;
            justify-content:center;
            height:100vh;
            text-align:center
        }
        .icon{
            font-size:4rem;
            margin-bottom:1rem
            }
        .title{
            font-size:1.5rem;
            color:#FFD700
            }
        .sub{
            color:#888;
            margin-top:.5rem
            }
    </style>
</head>
<body>
    <div>
        <div class="icon">📡</div>
        <div class="title">لا يوجد اتصال بالإنترنت</div>
        <div class="sub">تحقق من اتصالك وأعد المحاولة</div>
        <button onclick="location.reload()" style="margin-top:1.5rem;background:#FFD700;border:none;padding:.8rem 2rem;border-radius:25px;font-size:1rem;cursor:pointer;font-weight:bold">إعادة المحاولة</button>
</div>
</body>
</html>
