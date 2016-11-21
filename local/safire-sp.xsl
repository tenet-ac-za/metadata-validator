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
		<xsl:variable name="mdui" select="md:SPSSODescriptor/md:Extensions/mdui:UIInfo/mdui:Description[@xml:lang='en']"/>
		<xsl:variable name="desc" select="md:SPSSODescriptor/md:AttributeConsumingService/md:ServiceDescription[@xml:lang='en']"/>
		<xsl:if test="$mdui and $desc and $mdui != $desc">
				<xsl:call-template name="error">
						<xsl:with-param name="m">
								<xsl:text>mismatched xml:lang='en' Description: '</xsl:text>
								<xsl:value-of select="$mdui"/>
								<xsl:text>' in mdui vs. '</xsl:text>
								<xsl:value-of select="$odn"/>
								<xsl:text>' in ServiceDescription</xsl:text>
						</xsl:with-param>
				</xsl:call-template>
		</xsl:if>

		<xsl:variable name="mdui" select="md:SPSSODescriptor/md:Extensions/mdui:UIInfo/mdui:DisplayName[@xml:lang='en']"/>
		<xsl:variable name="desc" select="md:SPSSODescriptor/md:AttributeConsumingService/md:ServiceName[@xml:lang='en']"/>
		<xsl:if test="$mdui and $desc and $mdui != $desc">
				<xsl:call-template name="error">
						<xsl:with-param name="m">
								<xsl:text>mismatched xml:lang='en' DisplayName: '</xsl:text>
								<xsl:value-of select="$mdui"/>
								<xsl:text>' in mdui vs. '</xsl:text>
								<xsl:value-of select="$odn"/>
								<xsl:text>' in ServiceName</xsl:text>
						</xsl:with-param>
				</xsl:call-template>
		</xsl:if>
		<xsl:apply-templates/>
	</xsl:template>

</xsl:stylesheet>
