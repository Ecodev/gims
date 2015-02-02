<?php

namespace Application\View\Helper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * Google Analytics view helper
 */
class GoogleAnalytics extends \Zend\View\Helper\AbstractHtmlElement implements ServiceLocatorAwareInterface
{

    use \Zend\ServiceManager\ServiceLocatorAwareTrait;

    /**
     * Returns javascript code for Google Analytics if tracking code is configured
     * @return string
     */
    public function __invoke()
    {
        $config = $this->getServiceLocator()->getServiceLocator()->get('Config');

        if (isset($config['googleAnalyticsTrackingCode']) && $config['googleAnalyticsTrackingCode']) {
            $trackingCode = $config['googleAnalyticsTrackingCode'];

            return <<<STRING
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', '$trackingCode', 'auto');
</script>
STRING;
        } else {
            return '';
        }
    }
}
