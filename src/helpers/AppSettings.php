<?php

/**
 * AppSettings - Helper class for managing application-wide settings
 * 
 * Provides easy access to general application settings like
 * app name, organization info, logos, and contact details.
 */
class AppSettings
{
    /**
     * Get application short name
     */
    public static function getName(): string
    {
        return Settings::get('app_name', 'KomBi-TIP Apps');
    }

    /**
     * Get application full name
     */
    public static function getFullName(): string
    {
        return Settings::get('app_full_name', 'Sistem Komisi Bimbingan Skripsi');
    }

    /**
     * Get application short name (alias for getName)
     */
    public static function getShortName(): string
    {
        return Settings::get('app_short_name', 'KomBi-TIP');
    }

    /**
     * Get application version
     */
    public static function getVersion(): string
    {
        return Settings::get('app_version', '1.0.0');
    }

    /**
     * Get program study name (Prodi)
     */
    public static function getProdi(): string
    {
        return Settings::get('org_prodi', 'Program Studi Teknologi Industri Pertanian');
    }

    /**
     * Get faculty name (Fakultas)
     */
    public static function getFakultas(): string
    {
        return Settings::get('org_fakultas', 'Fakultas Teknologi Pertanian');
    }

    /**
     * Get university name
     */
    public static function getUniversitas(): string
    {
        return Settings::get('org_universitas', 'Universitas Jember');
    }

    /**
     * Get location/city
     */
    public static function getLocation(): string
    {
        return Settings::get('org_location', 'Jember');
    }

    /**
     * Get full organization name (Prodi, Fakultas, Universitas)
     */
    public static function getFullOrganizationName(): string
    {
        return self::getProdi() . ', ' . self::getFakultas() . ', ' . self::getUniversitas();
    }

    /**
     * Get organization info as array for PDF letterhead
     */
    public static function getOrganizationInfo(): array
    {
        return [
            self::getProdi(),
            self::getFakultas(),
            self::getUniversitas()
        ];
    }

    /**
     * Get application logo URL (hardcoded - not configurable)
     */
    public static function getLogoUrl(): string
    {
        return '/public/images/logo.png';
    }

    /**
     * Get letterhead logo path for PDF (hardcoded - not configurable)
     */
    public static function getLetterheadLogo(): string
    {
        return '/unej bw.png';
    }

    /**
     * Get favicon URL (hardcoded - not configurable)
     */
    public static function getFaviconUrl(): string
    {
        return '/public/images/favicon.ico';
    }

    /**
     * Get footer text
     */
    public static function getFooterText(): string
    {
        return Settings::get('app_footer_text', 'KomBi-TIP Apps');
    }

    /**
     * Get developer name
     */
    public static function getDeveloperName(): string
    {
        return Settings::get('app_developer_name', 'BS');
    }

    /**
     * Get developer URL
     */
    public static function getDeveloperUrl(): string
    {
        return Settings::get('app_developer_url', 'https://www.suryadharma.work/');
    }

    /**
     * Get admin contact email
     */
    public static function getAdminContact(): string
    {
        return Settings::get('app_admin_contact', 'admin@kombi.unej.ac.id');
    }

    /**
     * Get email from name (for email headers)
     */
    public static function getEmailFromName(): string
    {
        return Settings::get('email_from_name', self::getName());
    }

    /**
     * Get copyright year
     */
    public static function getCopyrightYear(): string
    {
        return date('Y');
    }

    /**
     * Get full app name with organization
     */
    public static function getFullAppName(): string
    {
        return self::getName() . ' - ' . self::getProdi();
    }

    /**
     * Get copyright text
     */
    public static function getCopyrightText(): string
    {
        return '&copy; ' . self::getCopyrightYear() . ' ' . self::getFooterText() . '. All rights reserved.';
    }

    /**
     * Get developer link HTML
     */
    public static function getDeveloperLink(): string
    {
        return '<a href="' . htmlspecialchars(self::getDeveloperUrl()) . '" target="_blank" rel="noopener">' . htmlspecialchars(self::getDeveloperName()) . '</a>';
    }

    /**
     * Clear settings cache (call after updating settings)
     */
    public static function clearCache(): void
    {
        Settings::clearCache();
    }
}
