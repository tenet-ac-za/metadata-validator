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

        This file contains checks for Service Providers
    -->

    <!-- Check mdui requirements -->
    <xsl:template match="md:SPSSODescriptor/md:Extensions/mdui:UIInfo[not(descendant::mdui:DisplayName)]">
        <xsl:call-template name="error">
            <xsl:with-param name="m">mdui:DisplayName MUST be set for service providers</xsl:with-param>
        </xsl:call-template>
        <xsl:apply-templates/>
    </xsl:template>
    <xsl:template match="md:SPSSODescriptor/md:Extensions/mdui:UIInfo[not(descendant::mdui:Description)]">
        <xsl:call-template name="error">
            <xsl:with-param name="m">mdui:Description MUST be set for service providers</xsl:with-param>
        </xsl:call-template>
    </xsl:template>
    <xsl:template match="md:SPSSODescriptor/md:Extensions/mdui:UIInfo[not(descendant::mdui:PrivacyStatementURL)]">
        <xsl:call-template name="error">
            <xsl:with-param name="m">mdui:PrivacyStatementURL MUST be set for service providers</xsl:with-param>
        </xsl:call-template>
        <xsl:apply-templates/>
    </xsl:template>
    <xsl:template match="md:SPSSODescriptor/md:Extensions/mdui:UIInfo[not(descendant::mdui:Logo)]">
        <xsl:call-template name="warning">
            <xsl:with-param name="m">It is strongly recommended that mdui:Logo be set for service providers</xsl:with-param>
        </xsl:call-template>
        <xsl:apply-templates/>
    </xsl:template>
    <xsl:template match="md:SPSSODescriptor/md:AttributeConsumingService[not(descendant::md:RequestedAttribute)]">
        <xsl:call-template name="error">
            <xsl:with-param name="m">RequestedAttributes MUST be set for service providers</xsl:with-param>
        </xsl:call-template>
    </xsl:template>
    <xsl:template match="md:SPSSODescriptor/md:AttributeConsumingService[not(descendant::md:ServiceName)]">
        <xsl:call-template name="error">
            <xsl:with-param name="m">ServiceName MUST be set for service providers</xsl:with-param>
        </xsl:call-template>
    </xsl:template>

    <!-- check that mdui matches service descriptors -->
    <xsl:template match="md:EntityDescriptor[md:SPSSODescriptor]">
        <xsl:variable name="mduidesc" select="md:SPSSODescriptor/md:Extensions/mdui:UIInfo/mdui:Description[@xml:lang='en']"/>
        <xsl:variable name="servicedesc" select="md:SPSSODescriptor/md:AttributeConsumingService/md:ServiceDescription[@xml:lang='en']"/>
        <xsl:variable name="mduiname" select="md:SPSSODescriptor/md:Extensions/mdui:UIInfo/mdui:DisplayName[@xml:lang='en']"/>
        <xsl:variable name="servicename" select="md:SPSSODescriptor/md:AttributeConsumingService/md:ServiceName[@xml:lang='en']"/>
        <xsl:if test="$mduidesc and $servicedesc and $mduidesc != $servicedesc">
                <xsl:call-template name="error">
                        <xsl:with-param name="m">
                                <xsl:text>mismatched xml:lang='en' Description: '</xsl:text>
                                <xsl:value-of select="$mduidesc"/>
                                <xsl:text>' in mdui vs. '</xsl:text>
                                <xsl:value-of select="$servicedesc"/>
                                <xsl:text>' in ServiceDescription</xsl:text>
                        </xsl:with-param>
                </xsl:call-template>
        </xsl:if>

        <xsl:if test="$mduiname and $servicename and $mduiname != $servicename">
                <xsl:call-template name="error">
                        <xsl:with-param name="m">
                                <xsl:text>mismatched xml:lang='en' DisplayName: '</xsl:text>
                                <xsl:value-of select="$mduiname"/>
                                <xsl:text>' in mdui vs. '</xsl:text>
                                <xsl:value-of select="$servicename"/>
                                <xsl:text>' in ServiceName</xsl:text>
                        </xsl:with-param>
                </xsl:call-template>
        </xsl:if>
        <xsl:apply-templates/>
    </xsl:template>

</xsl:stylesheet>
