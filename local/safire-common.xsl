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

        This file contains common checks for both Identity and Service Providers
    -->

    <!-- Check length of description of purpose -->
    <xsl:template match="mdui:Description">
        <xsl:if test="string-length(text()) > 140">
            <xsl:choose>
                <xsl:when test="string-length(text()) > 160">
                    <xsl:call-template name="error">
                        <xsl:with-param name="m">
                            <xsl:text>mdui:Description MUST be 160 chars or less (currently </xsl:text>
                            <xsl:value-of select="string-length(text())"/>
                            <xsl:text> characters)</xsl:text>
                        </xsl:with-param>
                    </xsl:call-template>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:call-template name="warning">
                        <xsl:with-param name="m">
                            <xsl:text>mdui:Description should be 140 chars or less (currently </xsl:text>
                            <xsl:value-of select="string-length(text())"/>
                            <xsl:text> characters)</xsl:text>
                        </xsl:with-param>
                    </xsl:call-template>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:if>
    </xsl:template>

    <!-- Check that there is no RegistrationInfo (note we have to disable Ian's check for this) -->
    <xsl:template match="mdrpi:RegistrationInfo">
        <xsl:choose>
            <xsl:when test="@registrationAuthority = 'https://safire.ac.za'">
                <xsl:call-template name="info">
                    <xsl:with-param name="m">RegistrationInfo indicates SAFIRE is authority</xsl:with-param>
                </xsl:call-template>
            </xsl:when>
            <xsl:when test="contains(@registrationAuthority, 'safire.ac.za')">
                <xsl:call-template name="error">
                    <xsl:with-param name="m">
                        <xsl:text>RegistrationInfo has invalid SAFIRE authority '</xsl:text>
                        <xsl:value-of select="@registrationAuthority"/>
                        <xsl:text>'</xsl:text>
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:call-template name="error">
                    <xsl:with-param name="m">RegistrationInfo should not be set by Participants</xsl:with-param>
                </xsl:call-template>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <!-- Check the metadata certificates -->
    <xsl:template match="ds:X509Certificate">
        <xsl:variable name="use" select="ancestor::md:KeyDescriptor/@use"/>
        <xsl:if test="php:functionString('xsltfunc::checkBase64', text()) = 0">
            <xsl:call-template name="error">
                <xsl:with-param name="m">
                    <xsl:text>X509Certificate </xsl:text>
                    <xsl:if test="$use">
                        <xsl:text>(use=</xsl:text>
                        <xsl:value-of select="$use"/>
                        <xsl:text>) </xsl:text>
                    </xsl:if>
                    <xsl:text>MUST be BASE64 encoded</xsl:text>
                </xsl:with-param>
            </xsl:call-template>
        </xsl:if>
        <xsl:if test="php:functionString('xsltfunc::checkCertSelfSigned',text()) = 0">
            <xsl:call-template name="warning">
                <xsl:with-param name="m">
                    <xsl:text>X509Certificate </xsl:text>
                    <xsl:if test="$use">
                        <xsl:text>(use=</xsl:text>
                        <xsl:value-of select="$use"/>
                        <xsl:text>) </xsl:text>
                    </xsl:if>
                    <xsl:text>should be self-signed. Got issuer of '</xsl:text>
                    <xsl:value-of select="php:functionString('xsltfunc::getCertIssuer',text())"/>
                    <xsl:text>'</xsl:text>
                </xsl:with-param>
            </xsl:call-template>
        </xsl:if>
        <xsl:if test="php:functionString('xsltfunc::checkCertIsCA',text()) = 1">
            <xsl:call-template name="warning">
                <xsl:with-param name="m">
                    <xsl:text>X509Certificate </xsl:text>
                    <xsl:if test="$use">
                        <xsl:text>(use=</xsl:text>
                        <xsl:value-of select="$use"/>
                        <xsl:text>) </xsl:text>
                    </xsl:if>
                    <xsl:text>is a certificate authority (basicConstraints=CA:TRUE)</xsl:text>
                </xsl:with-param>
            </xsl:call-template>
        </xsl:if>
        <xsl:if test="php:functionString('xsltfunc::getCertBits', text()) &lt; 2048">
            <xsl:call-template name="error">
                <xsl:with-param name="m">
                    <xsl:text>X509Certificate </xsl:text>
                    <xsl:if test="$use">
                        <xsl:text>(use=</xsl:text>
                        <xsl:value-of select="$use"/>
                        <xsl:text>) </xsl:text>
                    </xsl:if>
                    <xsl:text>key should be >= 2048 bits, found </xsl:text>
                    <xsl:value-of select="php:functionString('xsltfunc::getCertBits',text())"/>
                </xsl:with-param>
            </xsl:call-template>
        </xsl:if>
        <xsl:choose>
            <xsl:when test="php:functionString('xsltfunc::checkCertValid',text(),'from') = 0">
                <xsl:call-template name="warning">
                    <xsl:with-param name="m">
                        <xsl:text>X509Certificate </xsl:text>
                        <xsl:if test="$use">
                            <xsl:text>(use=</xsl:text>
                            <xsl:value-of select="$use"/>
                            <xsl:text>) </xsl:text>
                        </xsl:if>
                        <xsl:text>is not yet valid (begins </xsl:text>
                        <xsl:value-of select="php:functionString('xsltfunc::getCertDates',text(),'from')"/>
                        <xsl:text>)</xsl:text>
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:when>
            <xsl:when test="php:functionString('xsltfunc::checkCertValid',text(),'to') = 0">
                <xsl:call-template name="warning">
                    <xsl:with-param name="m">
                        <xsl:text>X509Certificate </xsl:text>
                        <xsl:if test="$use">
                            <xsl:text>(use=</xsl:text>
                            <xsl:value-of select="$use"/>
                            <xsl:text>) </xsl:text>
                        </xsl:if>
                        <xsl:text>has expired or expires within a year (ends </xsl:text>
                        <xsl:value-of select="php:functionString('xsltfunc::getCertDates',text(),'to')"/>
                        <xsl:text>)</xsl:text>
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:call-template name="info">
                    <xsl:with-param name="m">
                        <xsl:text>X509Certificate </xsl:text>
                        <xsl:if test="$use">
                            <xsl:text>(use=</xsl:text>
                            <xsl:value-of select="$use"/>
                            <xsl:text>) </xsl:text>
                        </xsl:if>
                        <xsl:text>validity: </xsl:text>
                        <xsl:value-of select="php:functionString('xsltfunc::getCertDates',text(),'both')"/>
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:otherwise>
        </xsl:choose>
        <xsl:apply-templates/>
    </xsl:template>

    <!-- Note about SAML1 (hub doesn't support it) -->
    <xsl:template match="md:IDPSSODescriptor[contains(@protocolSupportEnumeration, 'urn:oasis:names:tc:SAML:1.1:protocol')]|md:SPSSODescriptor[contains(@protocolSupportEnumeration, 'urn:oasis:names:tc:SAML:1.1:protocol')]">
        <xsl:call-template name="info">
            <xsl:with-param name="m">Metadata contains unused Shib/SAML1 bindings</xsl:with-param>
        </xsl:call-template>
        <xsl:apply-templates/>
    </xsl:template>

    <!-- Check ContactPerson email addresses -->
    <xsl:template match="md:ContactPerson[not(descendant::md:EmailAddress)]">
        <xsl:call-template name="error">
            <xsl:with-param name="m">
                <xsl:text>EmailAddress must be set for ContactPerson of type </xsl:text>
                <xsl:value-of select="@contactType"/>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:template>
    <xsl:template match="md:EmailAddress[not(starts-with(text(), 'mailto:'))]">
        <xsl:call-template name="error">
            <xsl:with-param name="m">
                <xsl:text>EmailAddress for ContactPerson of type </xsl:text>
                <xsl:value-of select='ancestor::md:ContactPerson/@contactType'/>
                <xsl:text> must be a URI starting with mailto:</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:template>
    <xsl:template match="md:EmailAddress[php:functionString('xsltfunc::checkEmailAddress', text()) = 0]">
        <xsl:call-template name="error">
            <xsl:with-param name="m">
                <xsl:value-of select="substring-after(text(), 'mailto:')"/>
                <xsl:text> is not a valid EmailAddress for ContactPerson of type </xsl:text>
                <xsl:value-of select='ancestor::md:ContactPerson/@contactType'/>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:template>


    <xsl:template match="md:EntityDescriptor">
        <!-- Organization must be set (Ian's rules do the rest once it is set) -->
        <xsl:if test="not(descendant::md:Organization)">
            <xsl:call-template name="error">
                <xsl:with-param name="m">Organization details MUST be set</xsl:with-param>
            </xsl:call-template>
        </xsl:if>

        <!-- Check ContactPerson requirements -->
        <xsl:if test="count(md:ContactPerson[@contactType='technical'])=0">
            <xsl:call-template name="error">
                <xsl:with-param name="m">ContactPerson of type technical MUST be set</xsl:with-param>
            </xsl:call-template>
        </xsl:if>
        <xsl:if test="count(md:ContactPerson[@contactType='technical'])>1">
            <xsl:call-template name="warning">
                <xsl:with-param name="m">More than one ContactPerson of type technical</xsl:with-param>
            </xsl:call-template>
        </xsl:if>
        <xsl:if test="count(md:ContactPerson[@contactType='support'])=0">
            <xsl:call-template name="warning">
                <xsl:with-param name="m">ContactPerson of type support should be set</xsl:with-param>
            </xsl:call-template>
        </xsl:if>
        <xsl:if test="count(md:ContactPerson[@contactType='support'])>1">
            <xsl:call-template name="warning">
                <xsl:with-param name="m">More than one ContactPerson of type support</xsl:with-param>
            </xsl:call-template>
        </xsl:if>
        <xsl:apply-templates/>
    </xsl:template>

    <xsl:template match="mdui:*[substring(name(), string-length(name()) - 2) = 'URL']">
        <!-- Check that *URL point at web servers that exist -->
        <xsl:if test="php:functionString('xsltfunc::checkURL', text()) = 0">
            <xsl:call-template name="error">
                <xsl:with-param name="m">
                    <xsl:value-of select='local-name()'/>
                    <xsl:text> is not a valid URL</xsl:text>
                </xsl:with-param>
            </xsl:call-template>
        </xsl:if>
    </xsl:template>

    <xsl:template match="md:OrganizationURL">
        <!-- Check that *URL point at web servers that exist -->
        <xsl:if test="php:functionString('xsltfunc::checkURL', text()) = 0">
            <xsl:call-template name="error">
                <xsl:with-param name="m">
                    <xsl:value-of select='local-name()'/>
                    <xsl:text> is not a valid URL</xsl:text>
                </xsl:with-param>
            </xsl:call-template>
        </xsl:if>
    </xsl:template>


    <!-- Check that mdui::Logos point at web servers that exist -->
    <xsl:template match="mdui:Logo">
        <xsl:choose>
            <xsl:when test="not(starts-with(text(),'https://'))">
                <xsl:call-template name="error">
                    <xsl:with-param name="m">
                        <xsl:value-of select='name()'/>
                        <xsl:text> location does not start with https://</xsl:text>
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:when>
            <xsl:when test="php:functionString('xsltfunc::checkURL', text()) = 0">
                <xsl:call-template name="error">
                    <xsl:with-param name="m">
                        <xsl:value-of select='name()'/>
                        <xsl:text> location is not a valid URL</xsl:text>
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:when>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="md:*[@Location]">
        <!-- Check @Location uses a valid certificate -->
        <xsl:choose>
            <xsl:when test="starts-with(@Location, 'https:') and php:functionString('xsltfunc::checkURLCert', @Location, 0) = 0">
                <xsl:call-template name="error">
                    <xsl:with-param name="m">
                        <xsl:value-of select='local-name()'/>
                        <xsl:text> Location SSL verification with cURL: </xsl:text>
                        <xsl:value-of select="php:functionString('xsltfunc::checkURLCert',@Location, 0, 1)"/>
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:when>
            <xsl:when test="starts-with(@Location, 'https:') and php:functionString('xsltfunc::checkURLCert', @Location, 1) = 0">
                <xsl:call-template name="warning">
                    <xsl:with-param name="m">
                        <xsl:value-of select='local-name()'/>
                        <xsl:text> Location fails modern-browser SSL tests: </xsl:text>
                        <xsl:value-of select="php:functionString('xsltfunc::checkURLCert',@Location, 1, 1)"/>
                        <xsl:text> See https://www.ssllabs.com/ssltest/?d=</xsl:text>
                        <xsl:value-of select="substring-before(substring-after(@Location, '://'), '/')"/>
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:when>
        </xsl:choose>

        <!-- Check that @Location point at web servers that exist -->
        <xsl:if test="php:functionString('xsltfunc::checkURL', @Location) = 0">
            <xsl:call-template name="error">
                <xsl:with-param name="m">
                    <xsl:value-of select='local-name()'/>
                    <xsl:text> Location is not a valid URL</xsl:text>
                </xsl:with-param>
            </xsl:call-template>
        </xsl:if>
    </xsl:template>

    <!-- Check entityID  -->
    <xsl:template match="md:EntityDescriptor[php:functionString('xsltfunc::checkURL', @entityID) = 0]">
        <xsl:call-template name="warning">
            <xsl:with-param name="m">
                <xsl:value-of select='@entityID'/>
                <xsl:text> entityID is not a valid URL (should use well-known location scheme)</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
        <xsl:apply-templates/>
    </xsl:template>

</xsl:stylesheet>
