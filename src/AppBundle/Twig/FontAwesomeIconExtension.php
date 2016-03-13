<?php
namespace AppBundle\Twig;

class FontAwesomeIconExtension extends \Twig_Extension
{
    /*
     * Key is the domain once everythings been stripped of
     * First element of the array is the font-awesome icon
     * Second element is the class to apply color to the parent element (defaults to blue background)
     *
     * See http://www.danielcarl.info/tools/font-awesome-coloured-brand-icons/ for more options
     *
     */
    protected $brands = [
        '500px' => ['fa-500px', 'fa-color-icons'],
        'amazon' => ['fa-amazon', 'fa-color-icons'],
        'apple' => ['fa-apple', 'fa-color-icons'],
        'dropbox' => ['fa-dropbox', 'fa-color-icons'],
        'facebook' => ['fa-facebook-official', 'fa-color-icons'],
        'github' => ['fa-github', 'fa-color-icons'],
        'google' => ['fa-google', 'fa-color-icons'],
        'paypal' => ['fa-paypal', 'fa-color-icons'],
        'soundcloud' => ['fa-soundcloud', 'fa-color-icons'],
        'vimeo' => ['fa-vimeo-square', 'fa-color-icons'],
    ];

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('fabrand', array($this, 'FABrandFilter')),
            new \Twig_SimpleFilter('fabrandcolor', array($this, 'FABrandColorFilter')),
        );
    }

    /**
     * Takes a URL of a site, returns the best guessed icon for the brand
     * @param $url
     * @return string
     */
    public function FABrandFilter($url)
    {

        $url = $this->simplifyUrl($url);
        if(array_key_exists($url, $this->brands))
        {
            return $this->brands[$url][0];
        }

        return 'fa-user-secret';
    }

    /**
     * Takes a URL of a site, returns the classes for the color of the brand icon
     * @param $url
     * @return string
     */
    public function FABrandColorFilter($url)
    {

        $url = $this->simplifyUrl($url);
        if(array_key_exists($url, $this->brands))
        {
            if(isset($this->brands[$url][1])) {
                return $this->brands[$url][1];
            }
        }

        return 'bg-blue';
    }


    private function simplifyUrl($url)
    {
        $domain = parse_url($url, PHP_URL_HOST);
        // If we don't have a schema, parse_url fails, so try with an empty schema
        if($domain === null) {
            $domain = parse_url('//'.$url, PHP_URL_HOST);
        }

        if($domain === null) return null;

        // Strip leading and trailing extras
        $domain = str_replace(['www.', 'ftp.', '.com'], '', $domain);

        return $domain;
    }

    public function getName()
    {
        return 'fontAwesome_Brand_Icon';
    }
}