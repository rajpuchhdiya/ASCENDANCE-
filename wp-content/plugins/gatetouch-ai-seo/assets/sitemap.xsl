<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0"
    xmlns:html="http://www.w3.org/TR/REC-html40"
    xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
    xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>

    <xsl:template match="/">
        <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
            <title>XML Sitemap - GateTouch AI SEO Optimizer</title>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
            <style type="text/css">
                *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    font-size: 14px;
                    color: #1f2937;
                    background: #f3f4f6;
                    line-height: 1.6;
                }

                /* ── Header ── */
                #header {
                    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
                    color: white;
                    padding: 28px 40px;
                    box-shadow: 0 4px 20px rgba(99,102,241,.3);
                }
                #header h1 {
                    font-size: 24px;
                    font-weight: 800;
                    letter-spacing: -.5px;
                    margin-bottom: 4px;
                }
                #header p {
                    font-size: 14px;
                    opacity: .85;
                    margin: 0;
                }
                #header .header-meta {
                    display: flex;
                    gap: 24px;
                    margin-top: 16px;
                    flex-wrap: wrap;
                }
                #header .header-stat {
                    background: rgba(255,255,255,.15);
                    padding: 8px 16px;
                    border-radius: 8px;
                    font-size: 13px;
                    backdrop-filter: blur(4px);
                }
                #header .header-stat strong { display: block; font-size: 20px; font-weight: 800; }

                /* ── Main Content ── */
                #content { max-width: 1200px; margin: 32px auto; padding: 0 20px 60px; }

                /* ── Info Box ── */
                .info-box {
                    background: #eef2ff;
                    border: 1px solid #c7d2fe;
                    border-radius: 12px;
                    padding: 16px 20px;
                    margin-bottom: 24px;
                    font-size: 13px;
                    color: #4338ca;
                    display: flex;
                    align-items: flex-start;
                    gap: 12px;
                }
                .info-box .icon { font-size: 20px; flex-shrink: 0; }

                /* ── Sitemap Navigation (for index) ── */
                .sitemap-nav {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                    gap: 16px;
                    margin-bottom: 32px;
                }
                .sitemap-nav-card {
                    background: white;
                    border: 1.5px solid #e5e7eb;
                    border-radius: 12px;
                    padding: 20px;
                    transition: all .2s;
                    box-shadow: 0 1px 3px rgba(0,0,0,.06);
                }
                .sitemap-nav-card:hover {
                    border-color: #6366f1;
                    box-shadow: 0 4px 16px rgba(99,102,241,.15);
                    transform: translateY(-2px);
                }
                .sitemap-nav-card a {
                    color: #6366f1;
                    font-weight: 700;
                    text-decoration: none;
                    font-size: 15px;
                }
                .sitemap-nav-card .card-meta {
                    font-size: 12px;
                    color: #9ca3af;
                    margin-top: 6px;
                }

                /* ── URL Table ── */
                .sitemap-card {
                    background: white;
                    border-radius: 12px;
                    box-shadow: 0 1px 4px rgba(0,0,0,.08);
                    overflow: hidden;
                    margin-bottom: 24px;
                }
                .sitemap-card__header {
                    background: #f9fafb;
                    border-bottom: 1px solid #e5e7eb;
                    padding: 14px 20px;
                    font-weight: 700;
                    font-size: 14px;
                    color: #374151;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                .url-count {
                    background: #6366f1;
                    color: white;
                    font-size: 11px;
                    font-weight: 700;
                    padding: 2px 8px;
                    border-radius: 20px;
                    margin-left: 4px;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                th {
                    background: #f9fafb;
                    text-align: left;
                    padding: 11px 16px;
                    font-size: 11px;
                    font-weight: 700;
                    text-transform: uppercase;
                    letter-spacing: .6px;
                    color: #6b7280;
                    border-bottom: 2px solid #e5e7eb;
                    white-space: nowrap;
                }
                td {
                    padding: 12px 16px;
                    border-bottom: 1px solid #f3f4f6;
                    font-size: 13px;
                    vertical-align: middle;
                }
                tr:last-child td { border-bottom: none; }
                tr:hover td { background: #fafbff; }

                td a {
                    color: #6366f1;
                    text-decoration: none;
                    word-break: break-all;
                    font-weight: 500;
                }
                td a:hover { text-decoration: underline; }

                /* Priority badges */
                .priority {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    width: 40px; height: 40px;
                    border-radius: 50%;
                    font-weight: 800;
                    font-size: 12px;
                    color: white;
                }
                .pri-high   { background: #10b981; }
                .pri-medium { background: #f59e0b; }
                .pri-low    { background: #9ca3af; }

                /* Frequency badge */
                .freq-badge {
                    font-size: 11px;
                    padding: 3px 8px;
                    border-radius: 20px;
                    font-weight: 600;
                    background: #f3f4f6;
                    color: #374151;
                    white-space: nowrap;
                }

                /* Image count */
                .img-count {
                    font-size: 11px;
                    color: #6b7280;
                    white-space: nowrap;
                }
                .img-thumb {
                    width: 36px; height: 36px;
                    border-radius: 6px;
                    object-fit: cover;
                    border: 1px solid #e5e7eb;
                }

                /* ── Footer ── */
                #footer {
                    text-align: center;
                    font-size: 12px;
                    color: #9ca3af;
                    padding: 20px;
                    border-top: 1px solid #e5e7eb;
                    margin-top: 40px;
                }
                #footer a { color: #6366f1; }

                /* ── Search / Filter ── */
                .filter-bar {
                    display: flex;
                    gap: 12px;
                    margin-bottom: 20px;
                    align-items: center;
                    flex-wrap: wrap;
                }
                #sitemap-search {
                    padding: 9px 14px;
                    border: 1.5px solid #e5e7eb;
                    border-radius: 8px;
                    font-size: 13px;
                    width: 280px;
                    outline: none;
                    transition: border-color .15s;
                }
                #sitemap-search:focus { border-color: #6366f1; }
                #url-count-display { font-size: 13px; color: #6b7280; }

                @media (max-width: 640px) {
                    #header { padding: 20px; }
                    #content { padding: 0 12px 40px; }
                    table { font-size: 12px; }
                    th, td { padding: 8px 10px; }
                    .sitemap-nav { grid-template-columns: 1fr; }
                }
            </style>
        </head>
        <body>

        <!-- Header -->
        <div id="header">
            <h1>🗺️ XML Sitemap</h1>
            <p>Generated by <strong>GateTouch AI SEO Optimizer</strong> - Helping search engines and AI systems discover your content</p>
            <div class="header-meta">
                <xsl:if test="count(sitemap:sitemapindex/sitemap:sitemap) > 0">
                    <div class="header-stat">
                        <strong><xsl:value-of select="count(sitemap:sitemapindex/sitemap:sitemap)"/></strong>
                        Sitemaps in Index
                    </div>
                </xsl:if>
                <xsl:if test="count(sitemap:urlset/sitemap:url) > 0">
                    <div class="header-stat">
                        <strong><xsl:value-of select="count(sitemap:urlset/sitemap:url)"/></strong>
                        URLs Indexed
                    </div>
                </xsl:if>
                <xsl:if test="count(sitemap:urlset/sitemap:url/image:image) > 0">
                    <div class="header-stat">
                        <strong><xsl:value-of select="count(sitemap:urlset/sitemap:url/image:image)"/></strong>
                        Images
                    </div>
                </xsl:if>
            </div>
        </div>

        <div id="content">

            <!-- Info box -->
            <div class="info-box">
                <span class="icon">ℹ️</span>
                <div>
                    This is an XML sitemap for search engines and AI crawlers.
                    It lists all publicly accessible pages on this website.
                    <strong>For humans:</strong> use the website navigation.
                    <strong>For SEO tools:</strong> submit <a href="/sitemap.xml">/sitemap.xml</a> to
                    Google Search Console and Bing Webmaster Tools.
                </div>
            </div>

            <!-- SITEMAP INDEX VIEW -->
            <xsl:if test="sitemap:sitemapindex">
                <div class="sitemap-card">
                    <div class="sitemap-card__header">
                        📂 Sitemap Index
                        <span class="url-count"><xsl:value-of select="count(sitemap:sitemapindex/sitemap:sitemap)"/> sitemaps</span>
                    </div>
                    <div class="sitemap-nav" style="padding:20px;">
                        <xsl:for-each select="sitemap:sitemapindex/sitemap:sitemap">
                            <xsl:sort select="sitemap:loc"/>
                            <div class="sitemap-nav-card">
                                <a href="{sitemap:loc}"><xsl:value-of select="sitemap:loc"/></a>
                                <div class="card-meta">
                                    Last modified: <xsl:value-of select="sitemap:lastmod"/>
                                </div>
                            </div>
                        </xsl:for-each>
                    </div>
                </div>
            </xsl:if>

            <!-- URL SET VIEW (posts/pages sitemap) -->
            <xsl:if test="sitemap:urlset">

                <!-- Search -->
                <div class="filter-bar">
                    <input type="text" id="sitemap-search" placeholder="🔍 Filter URLs…"
                           onkeyup="filterURLs(this.value)" />
                    <span id="url-count-display">
                        Showing <strong id="visible-count"><xsl:value-of select="count(sitemap:urlset/sitemap:url)"/></strong>
                        of <xsl:value-of select="count(sitemap:urlset/sitemap:url)"/> URLs
                    </span>
                </div>

                <div class="sitemap-card">
                    <div class="sitemap-card__header">
                        🔗 All URLs
                        <span class="url-count"><xsl:value-of select="count(sitemap:urlset/sitemap:url)"/> URLs</span>
                    </div>
                    <table id="url-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>URL</th>
                                <th>Last Modified</th>
                                <th>Change Freq</th>
                                <th>Priority</th>
                                <th>Images</th>
                            </tr>
                        </thead>
                        <tbody>
                            <xsl:for-each select="sitemap:urlset/sitemap:url">
                                <tr>
                                    <td style="color:#9ca3af; width:40px;">
                                        <xsl:value-of select="position()"/>
                                    </td>
                                    <td>
                                        <a href="{sitemap:loc}" target="_blank">
                                            <xsl:value-of select="sitemap:loc"/>
                                        </a>
                                    </td>
                                    <td style="white-space:nowrap; color:#6b7280; width:160px;">
                                        <xsl:value-of select="sitemap:lastmod"/>
                                    </td>
                                    <td style="width:100px;">
                                        <span class="freq-badge">
                                            <xsl:value-of select="sitemap:changefreq"/>
                                        </span>
                                    </td>
                                    <td style="width:60px; text-align:center;">
                                        <xsl:variable name="pri" select="sitemap:priority"/>
                                        <span class="priority">
                                            <xsl:attribute name="class">
                                                priority
                                                <xsl:choose>
                                                    <xsl:when test="$pri >= 0.8"> pri-high</xsl:when>
                                                    <xsl:when test="$pri >= 0.5"> pri-medium</xsl:when>
                                                    <xsl:otherwise> pri-low</xsl:otherwise>
                                                </xsl:choose>
                                            </xsl:attribute>
                                            <xsl:value-of select="sitemap:priority"/>
                                        </span>
                                    </td>
                                    <td style="width:80px;">
                                        <xsl:if test="image:image">
                                            <span class="img-count">
                                                🖼️ <xsl:value-of select="count(image:image)"/>
                                            </span>
                                        </xsl:if>
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </div>

                <!-- News entries -->
                <xsl:if test="sitemap:urlset/sitemap:url/news:news">
                <div class="sitemap-card">
                    <div class="sitemap-card__header">
                        📰 News Articles
                        <span class="url-count"><xsl:value-of select="count(sitemap:urlset/sitemap:url[news:news])"/> articles</span>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Article Title</th>
                                <th>URL</th>
                                <th>Published</th>
                            </tr>
                        </thead>
                        <tbody>
                            <xsl:for-each select="sitemap:urlset/sitemap:url[news:news]">
                                <tr>
                                    <td style="color:#9ca3af; width:40px;"><xsl:value-of select="position()"/></td>
                                    <td><strong><xsl:value-of select="news:news/news:title"/></strong></td>
                                    <td><a href="{sitemap:loc}" target="_blank"><xsl:value-of select="sitemap:loc"/></a></td>
                                    <td style="white-space:nowrap; color:#6b7280; width:180px;">
                                        <xsl:value-of select="news:news/news:publication_date"/>
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </div>
                </xsl:if>

            </xsl:if>

        </div><!-- /#content -->

        <div id="footer">
            Generated by <a href="https://wordpress.org/plugins/gatetouch-ai-seo/" target="_blank">GateTouch AI SEO Optimizer</a>
            - AI-powered SEO tools for WordPress.
            Submit your sitemap to
            <a href="https://search.google.com/search-console" target="_blank">Google Search Console</a> and
            <a href="https://www.bing.com/webmasters" target="_blank">Bing Webmaster Tools</a>.
        </div>

        <script type="text/javascript">
        function filterURLs(query) {
            var rows  = document.querySelectorAll('#url-table tbody tr');
            var lower = query.toLowerCase();
            var shown = 0;
            rows.forEach(function(row) {
                var url = row.querySelector('a') ? row.querySelector('a').textContent.toLowerCase() : '';
                if (url.indexOf(lower) !== -1 || lower === '') {
                    row.style.display = '';
                    shown++;
                } else {
                    row.style.display = 'none';
                }
            });
            var countEl = document.getElementById('visible-count');
            if (countEl) countEl.textContent = shown;
        }
        </script>

        </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
