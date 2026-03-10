import React from 'react';
import { Helmet } from 'react-helmet-async';
import { useQuery } from '@tanstack/react-query';
import { publicApi } from '@/api/public.api';

// Helper to resolve full url for resources
const getFileUrl = (path: string | null | undefined, fallback: string) => {
    if (!path) return fallback;
    if (path.startsWith('http')) return path;
    const baseUrl = (import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1').split('/api/v1')[0];
    return `${baseUrl}/storage/${path}`;
};

interface SEOHeadProps {
    title?: string;
    description?: string;
    keywords?: string;
    image?: string;
    url?: string;
}

const SEOHead: React.FC<SEOHeadProps> = ({
    title = 'SuryaMitra - PM Surya Ghar Muft Bijli Yojana Facilitation',
    description = 'Facilitating PM Surya Ghar Muft Bijli Yojana subsidy, registration, and installation for a greener future.',
    keywords = 'PM Surya Ghar, Solar Subsidy, Rooftop Solar India, Muft Bijli Yojana, Solar Installation',
    image = '/og-image.jpg',
    url = window.location.origin,
}) => {
    const { data: settings } = useQuery({
        queryKey: ['public-settings'],
        queryFn: publicApi.getSettings,
        staleTime: 1000 * 60 * 5, // Cache for 5 mins
    });

    const faviconUrl = getFileUrl(settings?.company_favicon, '/favicon.svg');
    const siteTitle = settings?.company_name ? `${settings.company_name} - ${title}` : (title.includes('SuryaMitra') ? title : `${title} | SuryaMitra`);

    const canonicalUrl = url + window.location.pathname;
    const organizationName = settings?.company_name || 'SuryaMitra';
    const organizationUrl = settings?.company_website || 'https://suryamitra.in';
    const logoUrl = getFileUrl(settings?.company_logo, '/logo.png');

    return (
        <Helmet>
            <title>{siteTitle}</title>
            <link rel="icon" href={faviconUrl} />
            <meta name="description" content={description} />
            <meta name="keywords" content={keywords} />

            {/* Open Graph / Facebook */}
            <meta property="og:type" content="website" />
            <meta property="og:url" content={canonicalUrl} />
            <meta property="og:title" content={siteTitle} />
            <meta property="og:description" content={description} />
            <meta property="og:image" content={image} />

            {/* Twitter */}
            <meta property="twitter:card" content="summary_large_image" />
            <meta property="twitter:url" content={canonicalUrl} />
            <meta property="twitter:title" content={siteTitle} />
            <meta property="twitter:description" content={description} />
            <meta property="twitter:image" content={image} />

            {/* Canonical Link */}
            <link rel="canonical" href={canonicalUrl} />

            {/* Structured Data (JSON-LD) */}
            <script type="application/ld+json">
                {JSON.stringify({
                    "@context": "https://schema.org",
                    "@type": "Organization",
                    "name": organizationName,
                    "url": organizationUrl,
                    "logo": logoUrl,
                    "description": description,
                    "sameAs": [
                        settings?.social_facebook || "https://facebook.com/suryamitra",
                        settings?.social_twitter || "https://twitter.com/suryamitra"
                    ]
                })}
            </script>
        </Helmet>
    );
};

export default SEOHead;
