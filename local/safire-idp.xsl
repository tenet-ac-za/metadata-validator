<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
    xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata"
    xmlns:mdui="urn:oasis:names:tc:SAML:metadata:ui"
    xmlns:mdxURL="xalan://uk.ac.sdss.xalan.md.URLchecker"
    xmlns:set="http://exslt.org/sets"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:shibmd="urn:mace:shibboleth:metadata:1.0"
    xmlns:mdrpi="urn:oasis:names:tc:SAML:metadata:rpi"
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

        This file contains checks for Identity Providers
    -->

    <!-- Check scope -->
    <xsl:template match="shibmd:Scope[.='ac.za']">
        <xsl:call-template name="error">
            <xsl:with-param name="m">bare 'ac.za' scope not permitted</xsl:with-param>
        </xsl:call-template>
    </xsl:template>
    <xsl:template match="shibmd:Scope[not(contains(., 'ac.za'))]">
        <xsl:call-template name="warning">
            <xsl:with-param name="m">
                <xsl:text>scope contains a non 'ac.za' domain of '</xsl:text>
                <xsl:value-of select="."/>
                <xsl:text>'</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:template>

    <!-- Check mdui recommendations -->
    <xsl:template match="md:IDPSSODescriptor/md:Extensions/mdui:UIInfo[not(descendant::mdui:DisplayName)]">
        <xsl:call-template name="warning">
            <xsl:with-param name="m">mdui:DisplayName should be set for identity providers</xsl:with-param>
        </xsl:call-template>
        <xsl:apply-templates/>
    </xsl:template>
    <xsl:template match="md:IDPSSODescriptor/md:Extensions/mdui:UIInfo[not(descendant::mdui:Description)]">
        <xsl:call-template name="warning">
            <xsl:with-param name="m">mdui:Description should be set for identity providers</xsl:with-param>
        </xsl:call-template>
        <xsl:apply-templates/>
    </xsl:template>
    <xsl:template match="md:IDPSSODescriptor/md:Extensions/mdui:UIInfo[not(descendant::mdui:PrivacyStatementURL)]">
        <xsl:call-template name="info">
            <xsl:with-param name="m">It is recommended that mdui:PrivacyStatementURL be set for identity providers</xsl:with-param>
        </xsl:call-template>
        <xsl:apply-templates/>
    </xsl:template>
    <xsl:template match="md:IDPSSODescriptor/md:Extensions/mdui:UIInfo[not(descendant::mdui:Logo)]">
        <xsl:call-template name="warning">
            <xsl:with-param name="m">It is strongly recommended that mdui:Logo be set for identity providers</xsl:with-param>
        </xsl:call-template>
        <xsl:apply-templates/>
    </xsl:template>

    <!-- Note about SingleLogoutService -->
    <xsl:template match="md:SingleLogoutService">
        <xsl:call-template name="info">
            <xsl:with-param name="m">SingleLogoutService is not supported properly :-(</xsl:with-param>
        </xsl:call-template>
    </xsl:template>

</xsl:stylesheet>
