# Fonts

Back to root overview: [README.md](../README.md#in-depth-documentation)

## Font Setup

When you install `tc-lib-pdf` as a dependency in your project (via `composer require` or `composer install`), the fonts from the companion package [`tc-lib-pdf-font`](https://github.com/tecnickcom/tc-lib-pdf-font) must be generated before they can be used.

Composer does not execute scripts declared by dependencies, so you need to add the font generation step to your **consuming project's** `composer.json` file:

```json
{
  "scripts": {
    "tc-lib-pdf-fonts": [
      "[ -d vendor/tecnickcom/tc-lib-pdf-font ] && make -C vendor/tecnickcom/tc-lib-pdf-font deps fonts || true"
    ],
    "post-install-cmd": [
      "@tc-lib-pdf-fonts"
    ],
    "post-update-cmd": [
      "@tc-lib-pdf-fonts"
    ]
  }
}
```

This ensures fonts are generated automatically when you run:

```bash
composer install
composer update
composer require ...
```

To also cover `composer dump-autoload` (used in many CI pipelines), add the hook to `post-autoload-dump` as well:

```json
"post-autoload-dump": [
    "@tc-lib-pdf-fonts"
]
```

If you prefer to generate fonts manually, run the build in the `tc-lib-pdf-font` package:

```bash
cd vendor/tecnickcom/tc-lib-pdf-font
make deps fonts
```

Equivalent one-liner from your project root:

```bash
make -C vendor/tecnickcom/tc-lib-pdf-font deps fonts
```

Once fonts are generated, they are cached in `vendor/tecnickcom/tc-lib-pdf-font/target/fonts/` and will not be regenerated unless explicitly rebuilt.

You can also add your own fonts and generate their PHP font data with `tc-lib-pdf-font`. For shared or immutable environments, generate them once into a persistent directory you control (outside `vendor/`) and point `K_PATH_FONTS` to that location.

For a runnable end-to-end custom font workflow, see [examples/E072_import_new_font.php](../examples/E072_import_new_font.php).

Example import commands (from the project root):

```bash
mkdir -p target/fonts/source target/fonts/custom

curl -fL --retry 3 -o target/fonts/source/NotoSans-Regular.ttf \
    https://github.com/notofonts/noto-fonts/raw/main/hinted/ttf/NotoSans/NotoSans-Regular.ttf

php vendor/tecnickcom/tc-lib-pdf-font/util/convert.php \
    --outpath=target/fonts/custom \
    --type=TrueTypeUnicode \
    --flags=32 \
    --encoding_id=1 \
    --fonts=target/fonts/source/NotoSans-Regular.ttf
```

Then point `K_PATH_FONTS` to `target/fonts/custom` (or an absolute path to that directory) before creating the `Tcpdf` instance.

```php
\define('K_PATH_FONTS', '/opt/app/fonts/tc-lib-pdf');
```

This avoids regenerating fonts on every dependency reinstall and lets multiple deployments reuse the same prepared font set.

## Third-Party Fonts

PHP font metadata files under the fonts directory are covered by the project license (GNU LGPL v3). They can be regenerated with the built-in font utilities.

Original source files are renamed for compatibility and compressed with PHP `gzcompress` (`.z` extension) where applicable.

| Prefix | Source | License |
|--------|--------|---------|
| `freefont` | [GNU FreeFont](https://ftp.gnu.org/gnu/freefont/freefont-ttf-20120503.zip) | GNU GPL v3 |
| `pdfa` | [tc-font-pdfa](https://github.com/tecnickcom/tc-font-pdfa) (derived from GNU FreeFont) | GNU GPL v3 |
| `dejavu` | [DejaVu Fonts 2.35](https://sourceforge.net/projects/dejavu/files/dejavu/2.35/dejavu-fonts-ttf-2.35.zip) | Bitstream Vera (with DejaVu public-domain changes) |
| `unifont` | [GNU Unifont 15.1.03](https://www.unifoundry.com/pub/unifont/unifont-15.1.03/unifont-15.1.03.tar.gz) | GPL v2+ with font embedding exception (also distributed under SIL OFL 1.1) |
| `cid0` | [GNU Unifont](http://unifoundry.com/unifont.html) (CID mappings) | GPL v2+ with font embedding exception |
| `core` | [Adobe Core14 AFM](https://partners.adobe.com/public/developer/en/pdf/Core14_AFMs.zip) | Adobe copyright terms (see AFM notices) |
