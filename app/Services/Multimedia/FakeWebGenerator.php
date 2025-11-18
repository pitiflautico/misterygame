<?php

namespace App\Services\Multimedia;

class FakeWebGenerator
{
    /**
     * Generate fake website HTML
     */
    public function generate(string $type, string $title, string $body, array $relatedLinks = []): string
    {
        return match ($type) {
            'news' => $this->generateNews($title, $body),
            'profile' => $this->generateProfile($title, $body),
            'article' => $this->generateArticle($title, $body),
            'blog' => $this->generateBlog($title, $body),
            default => $this->generateArticle($title, $body),
        };
    }

    /**
     * Generate fake news page
     */
    public function generateNews(string $headline, string $body, ?string $imageUrl = null): string
    {
        $imageHtml = $imageUrl ? "<img src='{$imageUrl}' alt='News Image' style='width:100%; max-width:800px; margin:20px 0;'>" : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$headline}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Georgia', serif; background: #f5f5f5; }
        .header { background: #333; color: white; padding: 20px; }
        .logo { font-size: 32px; font-weight: bold; }
        .breaking { background: #c00; color: white; padding: 5px 10px; display: inline-block; margin-top: 10px; }
        .content { max-width: 800px; margin: 30px auto; background: white; padding: 40px; }
        .headline { font-size: 36px; font-weight: bold; margin-bottom: 20px; line-height: 1.2; }
        .meta { color: #666; margin-bottom: 20px; font-size: 14px; }
        .body { line-height: 1.8; font-size: 18px; }
        .footer { background: #333; color: white; padding: 20px; text-align: center; margin-top: 50px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">NOTICIAS DIARIAS</div>
        <div class="breaking">ÚLTIMA HORA</div>
    </div>
    <div class="content">
        <h1 class="headline">{$headline}</h1>
        <div class="meta">Publicado hoy | Redacción</div>
        {$imageHtml}
        <div class="body">
            {$body}
        </div>
    </div>
    <div class="footer">
        © 2024 Noticias Diarias - Este es contenido ficticio para fines del juego
    </div>
</body>
</html>
HTML;
    }

    /**
     * Generate fake profile page
     */
    protected function generateProfile(string $name, string $bio): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$name} - Perfil</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #18191a; color: #e4e6eb; }
        .header { background: #242526; padding: 10px 20px; border-bottom: 1px solid #3a3b3c; }
        .container { max-width: 900px; margin: 20px auto; }
        .profile-header { background: #242526; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .avatar { width: 150px; height: 150px; background: #3a3b3c; border-radius: 50%; margin-bottom: 20px; }
        .name { font-size: 32px; font-weight: bold; margin-bottom: 10px; }
        .bio { color: #b0b3b8; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="header">
        <h2>RedSocial</h2>
    </div>
    <div class="container">
        <div class="profile-header">
            <div class="avatar"></div>
            <div class="name">{$name}</div>
            <div class="bio">{$bio}</div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Generate fake article page
     */
    protected function generateArticle(string $title, string $body): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Georgia', serif; background: #fff; }
        .container { max-width: 700px; margin: 50px auto; padding: 20px; }
        h1 { font-size: 42px; margin-bottom: 30px; line-height: 1.2; }
        .body { font-size: 18px; line-height: 1.8; color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <h1>{$title}</h1>
        <div class="body">{$body}</div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Generate fake blog page
     */
    protected function generateBlog(string $title, string $body): string
    {
        return $this->generateArticle($title, $body);
    }
}
