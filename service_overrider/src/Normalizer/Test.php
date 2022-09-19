<?php

namespace Drupal\service_overrider\Normalizer;

// require_once('modules/contrib/devel/kint/kint/Kint.class.php');

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\serialization\Normalizer\NormalizerBase;
use Facebook\InstantArticles\Elements\Image as FacebookImage;
use Symfony\Component\HttpFoundation\Request;

/**
 * Extends the content entity normalizer that ships with the base module.
 *
 * Supports the wrapping RSS scaffolding for outputting an RSS feed.
 */
class Test extends NormalizerBase {
  use EntityHelperTrait;

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = 'Drupal\Core\Entity\ContentEntityInterface';

  /**
   * {@inheritdoc}
   */
  protected $format = 'fbia_rss';

  /**
   * ContentEntityNormalizer constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   Config factory service.
   */
  public function __construct(ConfigFactoryInterface $config) {
    $this->config = $config->get('fb_instant_articles.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($data, $format = NULL, array $context = []) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $data */

    // \Drupal::logger('service_overrider')->notice('Module Invoked');

    $normalized = [
      'title' => $data->label(),
      'link' => $this->entityCanonicalUrl($data),
      'guid' => $data->uuid(),
      'content:encoded' => $this->serializer->normalize($data, 'fbia', $context),
    ];

    // Custom Alterations to the code
    $base_url = Request::createFromGlobals()->getSchemeAndHttpHost();
    foreach ($normalized['content:encoded']->getChildren() as $key => $value) {
        
        if ($value instanceof FacebookImage) {

            if ( !(\strpos($value->getUrl(), $base_url) === 0) ) {
                $value->withUrl($base_url . $value->getUrl());
            }
        }
    }
    
    // Add author if applicable.
    if ($author = $this->entityAuthor($data)) {
      $normalized['author'] = $author;
    }
    // Add created date if applicable.
    if ($created = $this->entityCreatedTime($data)) {
      $normalized['created'] = $created->format('c');
    }
    // Add changed date if applicable.
    if ($changed = $this->entityChangedTime($data)) {
      $normalized['modified'] = $changed->format('c');
    }

    return $normalized;
  }

}
