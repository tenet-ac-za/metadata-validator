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

        This file contains checks for blank elements, as suggested on the FOG list.
        Our definition of blank exceeds xs:string, since a string containing only
        whitespace will be considered blank for these purposes (PHP's trim() function).
    -->

    <xsl:template match="md:Company[php:functionString('XsltFunc::checkStringIsBlank', text()) = 1]">
        <xsl:call-template name="error">
            <xsl:with-param name="m">md:Company must not be empty</xsl:with-param>
        </xsl:call-template>
    </xsl:template>

    <xsl:template match="md:GivenName[php:functionString('XsltFunc::checkStringIsBlank', text()) = 1]">
        <xsl:call-template name="error">
            <xsl:with-param name="m">md:GivenName must not be empty</xsl:with-param>
        </xsl:call-template>
    </xsl:template>

    <xsl:template match="md:OrganizationDisplayName[php:functionString('XsltFunc::checkStringIsBlank', text()) = 1]">
        <xsl:call-template name="error">
            <xsl:with-param name="m">md:OrganizationDisplayName must not be empty</xsl:with-param>
        </xsl:call-template>
    </xsl:template>

    <xsl:template match="md:OrganizationName[php:functionString('XsltFunc::checkStringIsBlank', text()) = 1]">
        <xsl:call-template name="error">
            <xsl:with-param name="m">md:OrganizationName must not be empty</xsl:with-param>
        </xsl:call-template>
    </xsl:template>

    <xsl:template match="md:ServiceDescription[php:functionString('XsltFunc::checkStringIsBlank', text()) = 1]">
        <xsl:call-template name="error">
            <xsl:with-param name="m">md:ServiceDescription must not be empty</xsl:with-param>
        </xsl:call-template>
    </xsl:template>

    <xsl:template match="md:ServiceName[php:functionString('XsltFunc::checkStringIsBlank', text()) = 1]">
        <xsl:call-template name="error">
            <xsl:with-param name="m">md:ServiceName must not be empty</xsl:with-param>
        </xsl:call-template>
    </xsl:template>

    <xsl:template match="md:SurName[php:functionString('XsltFunc::checkStringIsBlank', text()) = 1]">
        <xsl:call-template name="error">
            <xsl:with-param name="m">md:SurName must not be empty</xsl:with-param>
        </xsl:call-template>
    </xsl:template>

    <xsl:template match="md:EmailAddress[php:functionString('XsltFunc::checkStringIsBlank', text()) = 1]">
        <xsl:call-template name="error">
            <xsl:with-param name="m">md:EmailAddress must not be empty</xsl:with-param>
        </xsl:call-template>
    </xsl:template>

    <xsl:template match="md:TelephoneNumber[php:functionString('XsltFunc::checkStringIsBlank', text()) = 1]">
        <xsl:call-template name="error">
            <xsl:with-param name="m">md:TelephoneNumber must not be empty</xsl:with-param>
        </xsl:call-template>
    </xsl:template>

    <xsl:template match="mdui:Description[php:functionString('XsltFunc::checkStringIsBlank', text()) = 1]">
        <xsl:call-template name="error">
            <xsl:with-param name="m">mdui:Description must not be empty</xsl:with-param>
        </xsl:call-template>
    </xsl:template>

    <xsl:template match="mdui:DisplayName[php:functionString('XsltFunc::checkStringIsBlank', text()) = 1]">
        <xsl:call-template name="error">
            <xsl:with-param name="m">mdui:DisplayName must not be empty</xsl:with-param>
        </xsl:call-template>
    </xsl:template>

    <xsl:template match="mdui:DomainHint[php:functionString('XsltFunc::checkStringIsBlank', text()) = 1]">
        <xsl:call-template name="error">
            <xsl:with-param name="m">mdui:DomainHint must not be empty</xsl:with-param>
        </xsl:call-template>
    </xsl:template>

    <xsl:template match="mdui:GeolocationHint[php:functionString('XsltFunc::checkStringIsBlank', text()) = 1]">
        <xsl:call-template name="error">
            <xsl:with-param name="m">mdui:GeolocationHint must not be empty</xsl:with-param>
        </xsl:call-template>
    </xsl:template>

    <xsl:template match="mdui:IPHint[php:functionString('XsltFunc::checkStringIsBlank', text()) = 1]">
        <xsl:call-template name="error">
            <xsl:with-param name="m">mdui:IPHint must not be empty</xsl:with-param>
        </xsl:call-template>
    </xsl:template>

    <xsl:template match="mdui:Keywords[php:functionString('XsltFunc::checkStringIsBlank', text()) = 1]">
        <xsl:call-template name="error">
            <xsl:with-param name="m">mdui:Keywords must not be empty</xsl:with-param>
        </xsl:call-template>
    </xsl:template>

    <xsl:template match="shibmd:Scope[php:functionString('XsltFunc::checkStringIsBlank', text()) = 1]">
        <xsl:call-template name="error">
            <xsl:with-param name="m">shibmd:Scope must not be empty</xsl:with-param>
        </xsl:call-template>
    </xsl:template>

</xsl:stylesheet>
