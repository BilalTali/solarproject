<?php

namespace App\Http\Controllers;

class SitemapController extends Controller
{
    public function index()
    {
        $baseUrl = config('app.url');

        // Public pages
        $urls = [
            ['loc' => $baseUrl.'/', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => $baseUrl.'/documents', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => $baseUrl.'/agent/register', 'priority' => '0.9', 'changefreq' => 'monthly'],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $xml .= '<url>';
            $xml .= '<loc>'.$this->xml_escape($url['loc']).'</loc>';
            $xml .= '<lastmod>'.date('c').'</lastmod>';
            $xml .= '<changefreq>'.$url['changefreq'].'</changefreq>';
            $xml .= '<priority>'.$url['priority'].'</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }

    public function robots()
    {
        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Disallow: /api/v1/admin/*\n";
        $content .= "Disallow: /api/v1/agent/*\n";
        $content .= "Disallow: /api/v1/super-agent/*\n";
        $content .= "\nSitemap: ".config('app.url').'/sitemap.xml';

        return response($content, 200)->header('Content-Type', 'text/plain');
    }

    private function xml_escape($string)
    {
        return htmlspecialchars($string, ENT_XML1, 'UTF-8');
    }
}
