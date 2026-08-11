<?php
/**
 * Secure Pure PHP PDF Exporter Engine for Ascendance Intelligence Platform
 *
 * @package Ascendance\Core
 */

namespace Ascendance\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PDF_Exporter {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {}

    /**
     * Generate PDF binary stream for an authorized Brief or Dossier
     *
     * @param int $post_id Post ID
     * @param int $user_id User ID
     * @return string Binary PDF content
     */
    public function generate_pdf( $post_id, $user_id = 0 ) {
        $post = get_post( $post_id );
        if ( ! $post || ! in_array( $post->post_type, array( 'brief', 'dossier' ), true ) ) {
            return '';
        }

        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        // Verify Paywall clearance
        if ( class_exists( 'Ascendance\Core\Paywall' ) && ! Paywall::get_instance()->user_has_access( $post_id, $user_id ) ) {
            return '';
        }

        $title       = get_the_title( $post_id );
        $type_label  = get_post_type_labels( get_post_type_object( $post->post_type ) )->singular_name ?? ucfirst( $post->post_type );
        $pub_date    = get_the_date( 'F j, Y', $post_id );
        
        $t_terms     = wp_get_post_terms( $post_id, 'topic', array( 'fields' => 'names' ) );
        $topic_str   = ( ! is_wp_error( $t_terms ) && ! empty( $t_terms ) ) ? implode( ', ', $t_terms ) : 'General Intelligence';

        $r_terms     = wp_get_post_terms( $post_id, 'region', array( 'fields' => 'names' ) );
        $region_str  = ( ! is_wp_error( $r_terms ) && ! empty( $r_terms ) ) ? implode( ', ', $r_terms ) : 'Central Africa';

        $tier_access = get_field( 'tier_access', $post_id ) ?: 'Essential';
        $dek         = get_field( 'dek', $post_id ) ?: get_the_excerpt( $post_id );
        $content     = wp_strip_all_tags( apply_filters( 'the_content', $post->post_content ) );

        // Sanitize and clean text for PDF streams
        $clean_title   = $this->sanitize_pdf_text( $title );
        $clean_dek     = $this->sanitize_pdf_text( $dek );
        $clean_topic   = $this->sanitize_pdf_text( $topic_str );
        $clean_region  = $this->sanitize_pdf_text( $region_str );
        $clean_content = $this->sanitize_pdf_text( $content );

        // Build native PDF v1.4 objects
        $pdf = "%PDF-1.4\n";
        $pdf .= "%\xE2\xE3\xCF\xD3\n";

        $objects = array();

        // 1: Catalog
        $objects[1] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";

        // 2: Pages
        $objects[2] = "2 0 obj\n<< /Type /Pages /Kids [ 3 0 R ] /Count 1 >>\nendobj\n";

        // Font Helvetica
        $objects[4] = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>\nendobj\n";
        $objects[5] = "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>\nendobj\n";

        // Stream Content
        $stream = "BT\n";
        
        // Header Banner
        $stream .= "/F2 10 Tf 50 770 Td (ASCENDANCE INTELLIGENCE & ADVISORY DESK) Tj\n";
        $stream .= "/F1 9 Tf 0 -14 Td (OFFICIAL SUBSCRIBER INTELLIGENCE EXPORT) Tj\n";
        $stream .= "0 -10 Td (----------------------------------------------------------------------------------------------------) Tj\n";

        // Category & Date
        $stream .= "/F2 9 Tf 0 -20 Td (" . strtoupper( $type_label ) . " // " . strtoupper( $pub_date ) . ") Tj\n";
        
        // Title
        $stream .= "/F2 16 Tf 0 -24 Td (" . $this->escape_pdf_string( substr( $clean_title, 0, 70 ) ) . ") Tj\n";
        if ( strlen( $clean_title ) > 70 ) {
            $stream .= "/F2 16 Tf 0 -20 Td (" . $this->escape_pdf_string( substr( $clean_title, 70, 70 ) ) . ") Tj\n";
        }

        // Subtitle / Dek
        if ( ! empty( $clean_dek ) ) {
            $stream .= "/F1 11 Tf 0 -22 Td (" . $this->escape_pdf_string( substr( $clean_dek, 0, 85 ) ) . ") Tj\n";
            if ( strlen( $clean_dek ) > 85 ) {
                $stream .= "/F1 11 Tf 0 -15 Td (" . $this->escape_pdf_string( substr( $clean_dek, 85, 85 ) ) . ") Tj\n";
            }
        }

        // Metadata Box
        $stream .= "0 -20 Td (----------------------------------------------------------------------------------------------------) Tj\n";
        $stream .= "/F2 9 Tf 0 -14 Td (DESK: " . $this->escape_pdf_string( $clean_topic ) . "   |   REGION: " . $this->escape_pdf_string( $clean_region ) . "   |   CLEARANCE: " . strtoupper( $tier_access ) . ") Tj\n";
        $stream .= "0 -10 Td (----------------------------------------------------------------------------------------------------) Tj\n";

        // Main Executive Content Body (Wrapped Lines)
        $stream .= "/F1 10 Tf 0 -24 Td (EXECUTIVE BRIEFING & ANALYSIS:) Tj\n";
        $stream .= "/F1 9 Tf 0 -16 Td ()\n";

        $paragraphs = explode( "\n", $clean_content );
        $line_count = 0;
        foreach ( $paragraphs as $para ) {
            $para = trim( $para );
            if ( empty( $para ) ) continue;
            $chunks = str_split( $para, 85 );
            foreach ( $chunks as $chunk ) {
                if ( $line_count > 32 ) break 2;
                $stream .= "(" . $this->escape_pdf_string( $chunk ) . ") Tj T*\n";
                $line_count++;
            }
            $stream .= "T*\n";
            $line_count++;
        }

        // Security Footer
        $stream .= "0 50 Td /F2 8 Tf (CONFIDENTIAL & PROPRIETARY // ASCENDANCE RESEARCH // NOT FOR REDISTRIBUTION) Tj\n";
        $stream .= "ET\n";

        $stream_len = strlen( $stream );
        $objects[3] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [ 0 0 612 792 ] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>\nendobj\n";
        $objects[6] = "6 0 obj\n<< /Length $stream_len >>\nstream\n" . $stream . "endstream\nendobj\n";

        ksort( $objects );

        $offsets = array();
        $offset = strlen( $pdf );

        foreach ( $objects as $id => $obj_str ) {
            $offsets[$id] = $offset;
            $pdf .= $obj_str;
            $offset = strlen( $pdf );
        }

        $xref_offset = strlen( $pdf );
        $pdf .= "xref\n0 " . ( count( $objects ) + 1 ) . "\n";
        $pdf .= "0000000000 65535 f \n";

        foreach ( $objects as $id => $obj_str ) {
            $pdf .= sprintf( "%010d 00000 n \n", $offsets[$id] );
        }

        $pdf .= "trailer\n<< /Size " . ( count( $objects ) + 1 ) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xref_offset . "\n%%EOF\n";

        return $pdf;
    }

    /**
     * Stream generated PDF binary directly to browser
     *
     * @param int $post_id Post ID
     * @param int $user_id User ID
     */
    public function generate_and_stream( $post_id, $user_id = 0 ) {
        $pdf_content = $this->generate_pdf( $post_id, $user_id );
        if ( empty( $pdf_content ) ) {
            status_header( 403 );
            wp_die( 'Forbidden: You do not have permission to export this document.', 'PDF Export Error', array( 'response' => 403 ) );
        }

        $post_type = get_post_type( $post_id ) ?: 'intelligence';
        $filename  = 'Ascendance-' . ucfirst( $post_type ) . '-' . $post_id . '.pdf';

        if ( ! headers_sent() ) {
            header( 'Content-Type: application/pdf' );
            header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
            header( 'Content-Length: ' . strlen( $pdf_content ) );
            header( 'Cache-Control: private, no-store, max-age=0, must-revalidate' );
            header( 'Pragma: no-cache' );
        }

        echo $pdf_content;
        exit;
    }

    /**
     * Helper to sanitize text for WinAnsi PDF encoding
     */
    private function sanitize_pdf_text( $text ) {
        $text = str_replace( array( "\r\n", "\r" ), "\n", $text );
        $text = preg_replace( '/[^\x20-\x7E\x0A]/', '', $text );
        return $text;
    }

    /**
     * Helper to escape special PDF characters () \
     */
    private function escape_pdf_string( $str ) {
        return str_replace(
            array( '\\', '(', ')' ),
            array( '\\\\', '\\(', '\\)' ),
            $str
        );
    }
}
