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
	xmlns:mdattr="urn:oasis:names:tc:SAML:metadata:attribute"
	xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
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

		This file validates SAFIRE's internal metadata additions, which are
		almost certainly not of use to anyone else. They are mostly stored in
		entity attributes named within the urn:x-safire.ac.za: namespace.
	-->

	<!--
		Validate the basic structure of the urn:x-safire.ac.za: attributes
	-->

	<!-- check that FriendlyName is set properly (aesthetics really) -->
	<xsl:template match="mdattr:EntityAttributes/saml:Attribute[starts-with(@Name, 'urn:x-safire.ac.za:')]">
		<xsl:variable name="want" select="substring-after(@Name, 'urn:x-safire.ac.za:')"/>
		<xsl:choose>
			<xsl:when test="not(@FriendlyName)">
				<xsl:call-template name="error">
					<xsl:with-param name="m">
						<xsl:value-of select="@Name"/>
						<xsl:text> should have a FriendlyName of '</xsl:text>
						<xsl:value-of select="$want"/>
						<xsl:text>'</xsl:text>
					</xsl:with-param>
				</xsl:call-template>
			</xsl:when>
			<xsl:when test="@FriendlyName != $want">
				<xsl:call-template name="warning">
					<xsl:with-param name="m">
						<xsl:value-of select="@Name"/>
						<xsl:text> should have FriendlyName of '</xsl:text>
						<xsl:value-of select="$want"/>
						<xsl:text>' instead of '</xsl:text>
						<xsl:value-of select="@FriendlyName"/>
						<xsl:text>'</xsl:text>
					</xsl:with-param>
				</xsl:call-template>
			</xsl:when>
		</xsl:choose>
	</xsl:template>

	<!-- should only be one of each! -->
	<xsl:template match="md:Extensions/mdattr:EntityAttributes">
		<xsl:if test="count(saml:Attribute[@Name='urn:x-safire.ac.za:schacHomeOrganization'])>1">
			<xsl:call-template name="error">
				<xsl:with-param name="m">More than one urn:x-safire.ac.za:schacHomeOrganization EntityAttribute</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="count(saml:Attribute[@Name='urn:x-safire.ac.za:schacHomeOrganizationType'])>1">
			<xsl:call-template name="error">
				<xsl:with-param name="m">More than one urn:x-safire.ac.za:schacHomeOrganizationType EntityAttribute</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="count(saml:Attribute[@Name='urn:x-safire.ac.za:publish-to-edugain'])>1">
			<xsl:call-template name="error">
				<xsl:with-param name="m">More than one urn:x-safire.ac.za:publish-to-edugain EntityAttribute</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="count(saml:Attribute[@Name='urn:x-safire.ac.za:federation-tag'])>1">
			<xsl:call-template name="error">
				<xsl:with-param name="m">More than one urn:x-safire.ac.za:federation-tag EntityAttribute</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="count(saml:Attribute[@Name='urn:x-safire.ac.za:consent-disable'])>1">
			<xsl:call-template name="error">
				<xsl:with-param name="m">More than one urn:x-safire.ac.za:consent-disable EntityAttribute</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="count(saml:Attribute[@Name='urn:x-safire.ac.za:scoping-disable'])>1">
			<xsl:call-template name="error">
				<xsl:with-param name="m">More than one urn:x-safire.ac.za:scoping-disable EntityAttribute</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="count(saml:Attribute[@Name='urn:x-safire.ac.za:purpose-is-to'])>1">
			<xsl:call-template name="error">
				<xsl:with-param name="m">More than one urn:x-safire.ac.za:purpose-is-to EntityAttribute</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="count(saml:Attribute[@Name='urn:x-safire.ac.za:participation-agreement'])>1">
			<xsl:call-template name="error">
				<xsl:with-param name="m">More than one urn:x-safire.ac.za:participation-agreement EntityAttribute</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="count(saml:Attribute[@Name='urn:x-safire.ac.za:hidden'])>1">
			<xsl:call-template name="error">
				<xsl:with-param name="m">More than one urn:x-safire.ac.za:hidden EntityAttribute</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
		<!-- now call child elements -->
		<xsl:apply-templates/>
	</xsl:template>

	<!-- Some variables only apply to Identity Providers -->
	<xsl:template match="md:EntityDescriptor[md:SPSSODescriptor]">
		<xsl:if test="md:Extensions/mdattr:EntityAttributes/saml:Attribute[@Name='urn:x-safire.ac.za:scoping-disable']">
			<xsl:call-template name="error">
				<xsl:with-param name="m">urn:x-safire.ac.za:scoping-disable should not be set for Service Providers</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="md:Extensions/mdattr:EntityAttributes/saml:Attribute[@Name='urn:x-safire.ac.za:schacHomeOrganization']">
			<xsl:call-template name="error">
				<xsl:with-param name="m">urn:x-safire.ac.za:schacHomeOrganization should not be set for Service Providers</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="md:Extensions/mdattr:EntityAttributes/saml:Attribute[@Name='urn:x-safire.ac.za:schacHomeOrganizationType']">
			<xsl:call-template name="error">
				<xsl:with-param name="m">urn:x-safire.ac.za:schacHomeOrganizationType should not be set for Service Providers</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
		<!-- now process child elements -->
		<xsl:apply-templates/>
	</xsl:template>

	<!-- Some variables only apply to Service Providers -->
	<xsl:template match="md:EntityDescriptor[md:IDPSSODescriptor]">
		<xsl:if test="md:Extensions/mdattr:EntityAttributes/saml:Attribute[@Name='urn:x-safire.ac.za:consent-disable']">
			<xsl:call-template name="error">
				<xsl:with-param name="m">urn:x-safire.ac.za:consent-disable should not be set for Identity Providers</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="md:Extensions/mdattr:EntityAttributes/saml:Attribute[@Name='urn:x-safire.ac.za:purpose-is-to']">
			<xsl:call-template name="error">
				<xsl:with-param name="m">urn:x-safire.ac.za:purpose-is-to should not be set for Identity Providers</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
		<!-- now process child elements -->
		<xsl:apply-templates/>
	</xsl:template>

</xsl:stylesheet>