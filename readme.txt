=== Clean Image for Google Merchant (WC) ===
Contributors: merchantpure
Tags: woocommerce, google merchant, feed image, product image, ai
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.3.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a dedicated, policy-compliant product image field to WooCommerce products, designed for Google Merchant Center feeds.

== Description ==
If you are using WooCommerce and your website displays promotional product images — banners, text overlays, badges, stickers — your feed for **Google Merchant Center**, **Facebook/Meta Shops**, **Pinterest**, or **TikTok Shop** can be rejected instantly.

Google strictly requires **clean, neutral, non‑promotional main images**.  
But merchants often refuse to give up their beautiful promotional product graphics used on the website.

**This plugin, Clean Image for Google Merchant (WC), solves the problem elegantly.**  
You can keep using promotional images on the website while assigning *clean and policy‑safe images* exclusively to product feeds.

The free version allows you to upload separate feed images for Google.  
The **PRO version** takes this much further with full AI automation allowing you to upload separate feed images per channel.

=== Why this plugin exists ===
Merchants often say:
- “My feed keeps getting rejected because the product images have text!”  
- “Google doesn’t want my promotional overlays but I can’t remove them from my website!”  
- “I sell more with promotional images but Google Merchant blocks my products.”

Now you don’t have to choose.  
Your website can keep its marketing visuals while your feed stays fully compliant and approved.

=== Real-world results ===

Several WooCommerce merchants have faced sudden Google Merchant Center disapprovals due to promotional overlays on product images.

In real-world usage, this plugin has helped merchants:
- restore fully disapproved Google Merchant feeds in just a few days
- keep promotional images on their website without changing their design
- resume Google Ads campaigns based on approved Merchant Center feeds
- recover lost traffic and orders without rebuilding product images

This plugin was built to solve a real, recurring problem — not a theoretical one.

== KEY FEATURES ==

### ✅ FREE VERSION FEATURES
- Add dedicated feed images for:
  - Google Merchant Center
- Keeps your website promotional images intact
- Ensures feeds remain compliant with platform policies
- Works with any WooCommerce store
- Lightweight and fast

### ⭐ PRO VERSION FEATURES (Upgrade Recommended)
All free features **plus**:

#### 1. AI Clean Feed Image Generator  
Generates **1024×1024 transparent PNG** images optimized for product feeds (Google-compliant).  
Removes backgrounds, text overlays, promotional banners — perfect clean feed assets.

#### 2. OpenAI API Key Integration  
Allows automated AI generation directly inside WooCommerce.

#### 3. Global Default Images (Per Channel)  
Set fallback images for Google, Facebook, Pinterest, TikTok.

#### 4. Feed Image Bulk Editor  
Manage multi-channel feed images for all products at once:
- Upload
- Generate AI images
- Reset
- Edit multiple products efficiently

#### 5. Export Multi-Channel Image CSV  
Exports product IDs, titles, and all multi-channel meta fields for auditing or migration.

#### 6. PRO Metabox Screen Options  
Enable/disable extra feed metaboxes using native WordPress “Screen Options”.

#### 7. AI Clean Image Button Inside Product Page  
One click: **Generate Clean Image (AI)** → instantly creates a feed-safe image.

#### 8. Additional PRO Metaboxes for Facebook, Pinterest, TikTok  
Manage separate feed images per channel, including AI generation.

**Upgrade to PRO:** [https://davidepuzzo.cloud/6k6o9](https://davidepuzzo.cloud/6k6o9)

== Installation ==

1. Upload the plugin ZIP file via Plugins → Add New → Upload Plugin.
2. Click Install Now, then Activate.
3. Ensure WooCommerce is installed and activated.
4. Edit any product and look for the "Google Merchant – Clean Image" metabox in the sidebar.
5. Select or upload a clean product image (without text or logos) and save the product.

== Screenshots ==
1. Google Merchant Center feed image metabox
2. The “Screen Settings” panel in the WooCommerce product edit screen, where the “Google Merchant – Clean Image” meta box can be enabled or disabled.
3. OpenAI API key & global default images panel (PRO)
4. Feed Image Bulk Editor (PRO)
5. Export Multi-Channel Image CSV (PRO)
6. Screen Options showing PRO metabox toggles (PRO)
7. In-product Generate Clean Image (AI) button - WooCommerce product main image vs AI Clean Image (PRO) 
8. Facebook, Pinterest, TikTok feed image metaboxes (PRO)

== Frequently Asked Questions ==
= Does this plugin require WooCommerce? =

Yes. This plugin is built specifically for WooCommerce and requires WooCommerce to be installed and active in order to work.

It integrates directly with WooCommerce products and product data to manage separate feed images without modifying your original product images.

= Which WooCommerce versions is this plugin compatible with? =

This plugin is built specifically for WooCommerce and is regularly tested with the latest stable WooCommerce releases.

At the time of the latest update, the plugin has been tested up to **WooCommerce 10.4.x**, including the most recent patch releases. As with any WooCommerce extension, keeping WooCommerce up to date is recommended for the best compatibility.

= What is a "clean" image for Google Merchant? =
A clean image is a product picture without logos, watermarks, discount badges, promotional text or any overlays that violate Google Merchant Center image policies.

= Why should I use WooCommerce Google Merchant Clean Image? =
Because many stores want promotional images on their website, but Google Merchant requires neutral and compliant images. This plugin lets you keep both: promotional images on your storefront and clean images for Google Merchant Center feeds.

= Does the plugin modify or overwrite my original product images? =
No. The plugin does not edit, crop or clean your images. It only stores an additional reference (ID + URL) to a separate "clean" image you choose or upload.

= Why do I need separate feed images? =
Because platforms like Google Merchant reject images with overlays, text, watermarks, or promotional elements.

= What happens if I do not set a clean image for a product? =
If you do not set a clean image manually, the plugin falls back to the product featured image. You should still provide a true clean image to be fully compliant.

= Which meta keys are used by the base plugin? =
Base plugin meta keys:
- _gm_image_id
- _gm_image_url

= Is the plugin compatible with WP All Export / WP All Import? =
Yes. The clean image URL is stored as post meta and can be exported or imported using WP All Export/WP All Import.

= Does it work with CTX Feed, Product Feed PRO and other feed managers? =
Yes. Any feed manager that supports custom meta fields can use the "_gm_image_url" field.

= Can I bulk-edit clean images? =
Yes, using WP All Import, WP All Export and many others or custom scripts that update the meta keys.

= Does the plugin affect the frontend of my store? =
No. It only affects admin data and feed/export information, not the product display.

= What does the PRO version add? =
WooCommerce Clean Image PRO adds dedicated clean image fields for:
- Meta / Facebook & Instagram Shops
- Pinterest Catalog
- TikTok Shop
- AI Clean Feed Image Generator
- OpenAI API Key Integration
- Global Default Images (Per Channel)
- Feed Image Bulk Editor
- Export Multi-Channel Image CSV
- AI Clean Image Button Inside Product Page

Each platform has its own ID + URL meta keys and CSV export columns.

= Which meta keys does the PRO version use? =
PRO meta keys:
- Meta/Facebook: _gm_image_id_meta / _gm_image_url_meta
- Pinterest: _gm_image_id_pinterest / _gm_image_url_pinterest
- TikTok: _gm_image_id_tiktok / _gm_image_url_tiktok

= Can I add my own custom channels in the PRO version? =
Yes. Developers can use the "wc_gm_image_pro_channels" filter to add or modify channels and meta keys.

= Are AI/automatic image cleaning features included? =
Not in this FREE release. All images must be selected manually. AI/automatic image cleaning features is included in the PRO release.

= Does the PRO version require an OpenAI API Key? =
Yes, only for AI-generated clean images.

= I don’t want to pay for AI. Will the plugin still work? =
Absolutely yes. Using AI is completely optional. The plugin works perfectly even if you never use AI features or never spend a single cent on AI.
AI background removal is simply an extra tool to speed things up, but you are never forced to use it. You can always manually upload clean, feed-safe images that are different from your main WooCommerce product image.
Thanks to this plugin, you can manage separate images for Google Merchant, Facebook, Pinterest, and TikTok feeds — something that is not possible with standard WooCommerce alone. AI just makes the process faster, but manual image control works exactly the same.

= How much will the AI cost me? =
AI processing is extremely affordable. The cost is based ONLY on the image that OpenAI generates (a clean 1024×1024 image), not on the size of your original upload. Whether your product image is 500 KB or 18 MB, the AI generation cost stays almost the same. On average, a clean feed-ready image costs between **$0.02 and $0.06 per image**, depending on the OpenAI model used at the time.

= I’ve heard about OpenAI, but I don’t know where to get my API Key =
You can create an OpenAI account here: https://openai.com  
If you already have an account, log in here: https://platform.openai.com/login  
Once logged in, generate your API key on this page: https://platform.openai.com/api-keys

= Can I export or migrate my multi-channel images? =
Yes — with the PRO CSV export tool.

= Does this plugin improve SEO? =
Indirectly yes, because approved product listings increase your visibility on merchant platforms.

= What happens if I deactivate the plugin? =
The stored meta data remains in the database. Feeds may stop reading those fields, but nothing is deleted.

= What are the minimum requirements? =
- WordPress 5.0+
- WooCommerce 5.0+
- PHP 7.2+

== AI Pricing Explained ==

This plugin uses your own OpenAI API Key for AI-powered image processing.  
The cost depends only on the image that OpenAI generates (the clean 1024×1024 output), not on your original file size.

This means:
- A 500 KB image costs the same as an 18 MB image.
- You only pay for the final AI-generated image.
- Background removal and feed-safe image generation are extremely affordable.

Typical cost per image:
**$0.02 to $0.06 per AI-generated image**

Your usage depends on:
- How many products you process
- How often you regenerate clean images
- The OpenAI model used at that moment

This makes AI background removal a cost-effective solution even for large catalogs.

== Changelog ==
= 1.3.1 =
* Stable Version

= 1.3 =
* Bug fixed

= 1.2.3 =
* Added informational notice about the new PRO version.
* Improved description and compliance guidance for Google Merchant users.
* Bug fixed

= 1.2.2 =
* Restored the original metabox logic for max compatibility.
* Fixed cases where the metabox did not appear.
* Translated the UI into English and prepared localization.

= 1.2.1 =
* Improved JS/CSS loading.
* Added “Requires Plugins: woocommerce”.

= 1.2.0 =
* Initial release.
