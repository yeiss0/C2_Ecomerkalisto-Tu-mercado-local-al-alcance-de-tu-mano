import re

with open('ecomerkalisto.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Extract CSS
style_match = re.search(r'<style>(.*?)</style>', content, re.DOTALL)
if style_match:
    with open('public/css/style.css', 'w', encoding='utf-8') as f:
        f.write(style_match.group(1).strip())

# Extract Body (roughly)
body_match = re.search(r'<body>(.*?)<script>', content, re.DOTALL)
if body_match:
    body_content = body_match.group(1)
    
    # Let's write the view
    view_content = f"""
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcomerKalisto | Supermercado Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <link rel="stylesheet" href="/login_mvc/public/css/style.css">
</head>
<body>
{body_content}
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <!-- Basic functionality script -->
    <script>
        AOS.init();
    </script>
</body>
</html>
"""
    with open('views/home/index.php', 'w', encoding='utf-8') as f:
        f.write(view_content)
