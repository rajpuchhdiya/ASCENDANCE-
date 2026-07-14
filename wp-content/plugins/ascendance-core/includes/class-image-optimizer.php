<?php
/**
 * Image Optimizer Class for next-gen formats (WebP/AVIF)
 *
 * @package Ascendance\Core
 */

namespace Ascendance\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Image_Optimizer {

    /**
     * Singleton instance
     * @var Image_Optimizer|null
     */
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Class constructor
     */
    private function __construct() {
        // Automatically convert uploaded images to next-gen formats
        add_filter( 'wp_generate_attachment_metadata', array( $this, 'generate_nextgen_images' ), 10, 2 );

        // Clean up next-gen images when attachment is deleted
        add_action( 'delete_attachment', array( $this, 'delete_nextgen_images' ) );
    }

    /**
     * Hook to generate WebP and AVIF formats when image metadata is generated
     */
    public function generate_nextgen_images( $metadata, $attachment_id ) {
        // Ensure metadata contains file info
        if ( empty( $metadata['file'] ) ) {
            return $metadata;
        }

        $upload_dir = wp_upload_dir();
        $file_path = path_join( $upload_dir['basedir'], $metadata['file'] );

        // Generate next-gen images for the original full-size image
        $this->convert_to_nextgen( $file_path );

        // Generate next-gen images for all registered sub-sizes
        if ( ! empty( $metadata['sizes'] ) ) {
            $file_dir = dirname( $file_path );
            foreach ( $metadata['sizes'] as $size => $info ) {
                $sub_file_path = path_join( $file_dir, $info['file'] );
                $this->convert_to_nextgen( $sub_file_path );
            }
        }

        return $metadata;
    }

    /**
     * Delete WebP and AVIF files when original attachment is deleted
     */
    public function delete_nextgen_images( $attachment_id ) {
        $metadata = wp_get_attachment_metadata( $attachment_id );
        if ( empty( $metadata['file'] ) ) {
            return;
        }

        $upload_dir = wp_upload_dir();
        $file_path = path_join( $upload_dir['basedir'], $metadata['file'] );

        // Delete next-gen for full-size
        $this->delete_file_variants( $file_path );

        // Delete next-gen for sub-sizes
        if ( ! empty( $metadata['sizes'] ) ) {
            $file_dir = dirname( $file_path );
            foreach ( $metadata['sizes'] as $size => $info ) {
                $sub_file_path = path_join( $file_dir, $info['file'] );
                $this->delete_file_variants( $sub_file_path );
            }
        }
    }

    /**
     * Helper to convert a single image file to WebP and AVIF
     */
    private function convert_to_nextgen( $file_path ) {
        if ( ! file_exists( $file_path ) ) {
            return;
        }

        $path_info = pathinfo( $file_path );
        $extension = strtolower( $path_info['extension'] );

        if ( ! in_array( $extension, array( 'jpg', 'jpeg', 'png' ), true ) ) {
            return;
        }

        // Initialize GD image resource
        $image = null;
        if ( $extension === 'png' ) {
            if ( function_exists( 'imagecreatefrompng' ) ) {
                $image = @imagecreatefrompng( $file_path );
            }
        } else {
            if ( function_exists( 'imagecreatefromjpeg' ) ) {
                $image = @imagecreatefromjpeg( $file_path );
            }
        }

        if ( ! $image ) {
            return;
        }

        // Preserve alpha channel (transparency) for PNGs
        if ( $extension === 'png' ) {
            imagealphablending( $image, false );
            imagesavealpha( $image, true );
        }

        // Generate WebP (e.g. image.png -> image.png.webp)
        $webp_path = $file_path . '.webp';
        if ( function_exists( 'imagewebp' ) ) {
            @imagewebp( $image, $webp_path, 82 );
        }

        // Generate AVIF (e.g. image.png -> image.png.avif)
        $avif_path = $file_path . '.avif';
        if ( function_exists( 'imageavif' ) ) {
            @imageavif( $image, $avif_path, 80 );
        }

        imagedestroy( $image );
    }

    /**
     * Helper to delete next-gen file variants
     */
    private function delete_file_variants( $file_path ) {
        $webp_path = $file_path . '.webp';
        $avif_path = $file_path . '.avif';

        if ( file_exists( $webp_path ) ) {
            @unlink( $webp_path );
        }
        if ( file_exists( $avif_path ) ) {
            @unlink( $avif_path );
        }
    }
}
