<?php

namespace Otomaties\SocialMediaPictureGenerator;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @subpackage SocialMediaPictureGenerator/admin
 */

class Admin
{

    /**
     * Initialize the class and set its properties.
     *
     * @param      string    $pluginName       The name of this plugin.
     */
    public function __construct(private string $pluginName)
    {
    }

    public function generateSocialMediaPictures() : void
    {
        $nonce = isset($_POST['smp_nonce']) ? sanitize_text_field($_POST['smp_nonce']) : '';
        $referer = $_SERVER['HTTP_REFERER'];
        $uniqueId = substr(uniqid(), 0, 12);
        $contentDir = wp_upload_dir()['basedir']. '/otomaties-smp/' . $uniqueId . '/';

        if (!wp_verify_nonce($nonce, 'generate_social_media_picture')) {
            $referer = add_query_arg('error', 'nonce', $referer);
            wp_safe_redirect($referer);
            exit;
        }

        $uploadedFiles = $this->filterOutImages($_FILES['image_size']);
        if (empty($uploadedFiles)) {
            $referer = add_query_arg('error', 'no_image', $referer);
            wp_safe_redirect($referer);
            exit;
        }

        $files = [];
        foreach ($uploadedFiles as $sizeName => $file) {
            $imageInfo = getimagesize($file['tmp_name']);

            if (!$imageInfo) {
                $referer = add_query_arg('error', 'invalid_image', $referer);
                wp_safe_redirect($referer);
                exit;
            }

            $imagesize = $this->getImagesize($sizeName);
            $watermarkPath = wp_get_original_image_path($imagesize['overlay']['ID']);

            if ($watermarkPath === false) {
                $referer = add_query_arg('error', 'invalid_watermark', $referer);
                wp_safe_redirect($referer);
                exit;
            }

            $watermark = imagecreatefrompng($watermarkPath);
            $watermarkInfo = getimagesize($watermarkPath);

            if ($watermark === false || $watermarkInfo === false) {
                $referer = add_query_arg('error', 'invalid_watermark', $referer);
                wp_safe_redirect($referer);
                exit;
            }
         

            // Create a new image resource based on the uploaded file type
            switch ($imageInfo[2]) {
                case IMAGETYPE_JPEG:
                    $image = imagecreatefromjpeg($file['tmp_name']);
                    break;
                case IMAGETYPE_PNG:
                    $image = imagecreatefrompng($file['tmp_name']);
                    break;
                case IMAGETYPE_GIF:
                    $image = imagecreatefromgif($file['tmp_name']);
                    break;
                default:
                    die('Invalid image type');
            }

            if ($image === false) {
                $referer = add_query_arg('error', 'invalid_image', $referer);
                wp_safe_redirect($referer);
                exit;
            }

            // Get the image width and height
            $frameWidth = $watermarkInfo[0];
            $frameHeight = $watermarkInfo[1];
            
            $imageWidth = imagesx($image);
            $imageHeight = imagesy($image);

            $newImage = imagecreatetruecolor($frameWidth, $frameHeight);

            if (!$newImage) {
                throw new \Exception('Cannot initialize new GD image stream');
            }
            $backgroundColor = imagecolorallocate($newImage, 255, 255, 255);
            if ($backgroundColor === false) {
                $backgroundColor = 16777215;
            }
            imagefill($newImage, 0, 0, $backgroundColor);

            $frameAspectRatio = $frameWidth / $frameHeight;
            $imageAspectRatio = $imageWidth / $imageHeight;

            $newImageWidth = $frameWidth;
            $newImageHeight = $frameHeight;
            $offsetX = 0;
            $offsetY = 0;

            if ($imageAspectRatio == 1 && $frameAspectRatio == 1) { // img & frame are square
                // do nothing
            } elseif (( $imageAspectRatio > 1 && ($frameAspectRatio < 1 || $frameAspectRatio > 1) ) // phpcs:ignore Generic.Files.LineLength.TooLong -- img landscape & frame landscape or portrait phpcs:ignore
                || ( $imageAspectRatio >= 1 && $frameAspectRatio == 1 ) // img landscape & frame square
                || ( $imageAspectRatio == 1 && $frameAspectRatio < 1 ) // img square & frame portrait
            ) {
                $newImageHeight = $frameHeight;
                $newImageWidth = $imageWidth * ($newImageHeight / $imageHeight);

                if ($newImageWidth < $frameWidth) {
                    $newImageWidth = $frameWidth;
                    $newImageHeight = $imageHeight * ($newImageWidth / $imageWidth);
                }

                $offsetX = ($frameWidth - $newImageWidth) / 2;
                $offsetY = 0;
            } elseif (( $imageAspectRatio < 1 && ($frameAspectRatio < 1 || $frameAspectRatio > 1) ) // phpcs:ignore Generic.Files.LineLength.TooLong -- img portrait & frame landscape or portrait
                || ( $imageAspectRatio <= 1 && $frameAspectRatio == 1 ) // img portrait & frame square
                || ( $imageAspectRatio == 1 && $frameAspectRatio > 1 )  // img square & frame landscape
            ) {
                $newImageWidth = $frameWidth;
                $newImageHeight = $imageHeight * ($newImageWidth / $imageWidth);

                if ($newImageHeight < $frameHeight) {
                    $newImageHeight = $frameHeight;
                    $newImageWidth = $imageWidth * ($newImageHeight / $imageHeight);
                }

                $offsetX = 0;
                $offsetY = ($frameHeight - $newImageHeight) / 2;
            }

            imagecopyresampled(
                $newImage,
                $image,
                $offsetX,
                $offsetY,
                0,
                0,
                $newImageWidth,
                $newImageHeight,
                $imageWidth,
                $imageHeight
            );
            imagealphablending($newImage, true);
            imagesavealpha($newImage, true);
            imagecopy($newImage, $watermark, 0, 0, 0, 0, $frameWidth, $frameHeight);
            $mergedFilename = $contentDir . $sizeName . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            ;

            if (!file_exists($contentDir)) {
                mkdir($contentDir, 0755, true);
            }

            switch ($imageInfo[2]) {
                case IMAGETYPE_JPEG:
                    imagejpeg($newImage, $mergedFilename, 90);
                    break;
                case IMAGETYPE_PNG:
                    imagepng($newImage, $mergedFilename, 9);
                    break;
                case IMAGETYPE_GIF:
                    imagegif($newImage, $mergedFilename);
                    break;
            }
            if ($image !== false) {
                imagedestroy($image);
            }

            if ($newImage !== false) {
                imagedestroy($newImage);
            }

            if ($watermark !== false) {
                imagedestroy($watermark);
            }

            $files[] = $mergedFilename;
        }
        $zipFileName = $this->zipEm($files);
        if (!$zipFileName) {
            $referer = add_query_arg('error', 'zip', $referer);
            wp_safe_redirect($referer);
            exit;
        }
        $this->cleanUp($contentDir, $zipFileName);
    }

    private function cleanUp(string $contentDir, string $zipFileName) : void
    {
        $contenDirContent = glob($contentDir . '*');
        if ($contenDirContent) {
            array_map('unlink', $contenDirContent);
        }
        rmdir($contentDir);
        unlink($zipFileName);
    }

    /**
     * Filter out files that are not images
     *
     * @param array<string, array<string, mixed>> $files
     * @return array<string, array<string, mixed>>
     */
    private function filterOutImages(array $files) : array
    {
        $filteredImages = [];
        foreach ($files['name'] as $key => $value) {
            if ($files['error'][$key] !== UPLOAD_ERR_OK) {
                continue;
            }

            if (!getimagesize($files['tmp_name'][$key])) {
                continue;
            }
            $filteredImages[$key] = [
                'name' => $files['name'][$key],
                'type' => $files['type'][$key],
                'tmp_name' => $files['tmp_name'][$key],
                'error' => $files['error'][$key],
                'size' => $files['size'][$key],
            ];
        }
        return $filteredImages;
    }

    /**
     * Zip the files and serve them to the user
     *
     * @param array<string> $files Full paths to the files
     * @return string|false
     */
    private function zipEm(array $files) : string|false
    {
        $zip = new \ZipArchive();

        $zipFileName = 'images.zip';

        if ($zip->open($zipFileName, \ZipArchive::CREATE) === true) {
            foreach ($files as $file) {
                $zip->addFile($file, basename($file));
            }

            // Close the zip archive
            $zip->close();
            
            // Serve the zip file to the user
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
            readfile($zipFileName);
            return $zipFileName;
        }

        return false;
    }

    /**
     * Get the image size from the ACF repeater option
     *
     * @param string $sizeName
     * @return array<string, mixed>
     */
    private function getImagesize(string $sizeName) : array
    {
        $possibleImageSizes = get_field('smp_image_sizes', 'option');
        foreach ($possibleImageSizes as $possibleImageSize) {
            if (sanitize_title($possibleImageSize['name']) === $sizeName) {
                return $possibleImageSize;
            }
        }
        return [];
    }
}
