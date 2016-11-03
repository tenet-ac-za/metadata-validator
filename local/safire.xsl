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
	xmlns="urn:oasis:names:tc:SAML:2.0:metadata">

	<!--
		Common support functions.
	-->
	<xsl:import href="../rules/check_framework.xsl"/>

	<!-- Checks for IdPs -->
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

	<xsl:template match="md:IDPSSODescriptor/md:Extensions/mdui:UIInfo[not(descendant::mdui:DisplayName)]">
		<xsl:call-template name="warning">
			<xsl:with-param name="m">mdui:DisplayName should be set for identity providers</xsl:with-param>
		</xsl:call-template>
	</xsl:template>
	<xsl:template match="md:IDPSSODescriptor/md:Extensions/mdui:UIInfo[not(descendant::mdui:Description)]">
		<xsl:call-template name="warning">
			<xsl:with-param name="m">mdui:Description should be set for identity providers</xsl:with-param>
		</xsl:call-template>
	</xsl:template>
	<xsl:template match="md:IDPSSODescriptor/md:Extensions/mdui:UIInfo[not(descendant::mdui:PrivacyStatementURL)]">
		<xsl:call-template name="warning">
			<xsl:with-param name="m">mdui:PrivacyStatementURL should be set for identity providers</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<!-- Checks for SPs -->
	<xsl:template match="md:SPSSODescriptor/md:Extensions/mdui:UIInfo[not(descendant::mdui:DisplayName)]">
		<xsl:call-template name="error">
			<xsl:with-param name="m">mdui:DisplayName MUST be set for service providers</xsl:with-param>
		</xsl:call-template>
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

	<!-- Common checks -->
	<xsl:template match="md:ContactPerson[not(descendant::md:EmailAddress)]">
		<xsl:call-template name="error">
			<xsl:with-param name="m">
				<xsl:text>EmailAddress must be set for ContactPerson of type </xsl:text>
				<xsl:value-of select="@contactType"/>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>
	<xsl:template match="md:EntityDescriptor">
		<xsl:if test="not(descendant::md:Organization)">
			<xsl:call-template name="error">
				<xsl:with-param name="m">Organization details MUST be set</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
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

</xsl:stylesheet>