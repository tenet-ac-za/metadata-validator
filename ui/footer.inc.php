<?php

/**
 * Template footer for metadata-validator
 *
 * SAFIRE uses SimpleSAMLphp, and so we need to skin this to look somewhat
 * like our version of SimpleSAMLphp. As such, parts of this template are
 * loosely derived from {@link http://simplesamlphp.org/ SimpleSAMLphp}
 *
 * @author Guy Halse http://orcid.org/0000-0002-9388-8592
 * @copyright Copyright (c) 2016, Tertiary Education and Research Network of South Africa
 * @license https://github.com/tenet-ac-za/metadata-validator/blob/master/LICENSE MIT License
 */

// phpcs:disable Generic.Files.LineLength.TooLong
declare(strict_types=1);

if (file_exists(dirname(__DIR__) . '/local/config.inc.php')) {
    include_once(dirname(__DIR__) . '/local/config.inc.php');
}
?>
        </div>
      </div>
      <div id="push"></div>
      </main>
    </div>
    <div id="foot">
      <footer id="footer">
        <div class="wrap">
          <div class="center copyrights">
            <!-- these are SAFIRE-specific -->
            <ul>
              <li><a href="https://<?= constant('DOMAIN') ?>/safire/policy/privacy/">Privacy statement</a></li>
              <!-- <li><<a href="#cookiesettings">Cookies</a></li> -->
              <li><a href="https://<?= constant('DOMAIN') ?>/">SAFIRE - South African Identity Federation</a></li>
            </ul>
          </div>
        </div>
      </footer>
    </div>
  </body>

  <!-- these are the bits you need to keep in a new skin -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.3/jquery-ui.min.js" integrity="sha512-Ww1y9OuQ2kehgVWSD/3nhgfrb424O3802QYP/A5gPXoM4+rRjiKrjHdGxQKrMGQykmsJ/86oGdHszfcVgUr4hA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.33.2/ace.min.js" integrity="sha512-40pej1Lz2wywxd9lNJwJNSp9ekNFyX6wCmOzoaqIuUqexcjAUYqnhbg+fYUuPHzVyr5hshGv5FX8Om7yuTuWnA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script<?= $nonce ?? '' ?>>
  // see header.inc.php about CSS from JSdelivr
  ace.config.set("useStrictCSP", true);
  </script>
  <script src="validate.js"></script>
</html>

