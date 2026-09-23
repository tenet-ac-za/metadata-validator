<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:alg="urn:oasis:names:tc:SAML:metadata:algsupport"
    xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
    xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata"
    xmlns:mdui="urn:oasis:names:tc:SAML:metadata:ui"
    xmlns:mdxURL="xalan://uk.ac.sdss.xalan.md.URLchecker"
    xmlns:set="http://exslt.org/sets"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:shibmd="urn:mace:shibboleth:metadata:1.0"
    xmlns:mdrpi="urn:oasis:names:tc:SAML:metadata:rpi"
    xmlns:remd="http://refeds.org/metadata"
    xmlns:php="http://php.net/xsl"
    xmlns="urn:oasis:names:tc:SAML:2.0:metadata">

    <!--
        Common support functions.
    -->
    <xsl:import href="../rules/check_framework.xsl"/>

    <!--
        This XSLT does validation against SAFIRE federation registry rules.
        These are not SAML metadata syntax issues; they're SAFIRE conventions.
        As such, they may or may not be appropriate for other federations. YMMV.

        This file contains new recommendations.
    -->

    <!-- Recommend being explict about algorithm support -->
    <xsl:template match="md:EntityDescriptor[not(md:Extensions/alg:DigestMethod or md:Extensions/alg:SigningMethod)]">
        <xsl:call-template name="warning">
            <xsl:with-param name="m">It is recommended to explicitly indicate digest &amp; signing algorithm support</xsl:with-param>
        </xsl:call-template>
        <xsl:apply-templates/>
    </xsl:template>

    <!-- Recommend explicitly specifying key use -->
    <xsl:template match="md:KeyDescriptor[not(@use)]">
        <xsl:call-template name="warning">
            <xsl:with-param name="m">It is recommended to explicitly indicate KeyDescriptor use (signing or encryption)</xsl:with-param>
        </xsl:call-template>
        <xsl:apply-templates/>
    </xsl:template>

</xsl:stylesheet>
