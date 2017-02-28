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
		Validate the actual values of the urn:x-safire.ac.za: attributes
	-->

	<!-- schacHomeOrganizationType has a controlled vocabulary -->
	<xsl:template match="mdattr:EntityAttributes/saml:Attribute[@Name='urn:x-safire.ac.za:schacHomeOrganizationType']">
		<xsl:variable name="allowed" select="'urn:schac:homeOrganizationType:int:university|urn:schac:homeOrganizationType:int:NREN|urn:schac:homeOrganizationType:int:other|urn:schac:homeOrganizationType:za:research-council'"/>
		<xsl:if test="not(contains(concat('|', $allowed, '|'), concat('|', saml:AttributeValue[1], '|')))">
			<xsl:call-template name="error">
				<xsl:with-param name="m">
					<xsl:text>urn:x-safire.ac.za:schacHomeOrganizationType has a controlled vocabulary, which does not include '</xsl:text>
					<xsl:value-of select="saml:AttributeValue[1]"/>
					<xsl:text>'</xsl:text>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

	<!-- publish-to-edugain has a controlled vocabulary -->
	<xsl:template match="mdattr:EntityAttributes/saml:Attribute[@Name='urn:x-safire.ac.za:publish-to-edugain']">
		<xsl:variable name="allowed" select="'yes|no|true|false'"/>
		<xsl:if test="not(contains(concat('|', $allowed, '|'), concat('|', saml:AttributeValue[1], '|')))">
			<xsl:call-template name="error">
				<xsl:with-param name="m">
					<xsl:text>urn:x-safire.ac.za:publish-to-edugain should be true/false, not '</xsl:text>
					<xsl:value-of select="saml:AttributeValue[1]"/>
					<xsl:text>'</xsl:text>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

	<!-- participation-agreement has a controlled vocabulary -->
	<xsl:template match="mdattr:EntityAttributes/saml:Attribute[@Name='urn:x-safire.ac.za:participation-agreement']">
		<xsl:variable name="allowed" select="'yes|no|true|false'"/>
		<xsl:if test="not(contains(concat('|', $allowed, '|'), concat('|', saml:AttributeValue[1], '|')))">
			<xsl:call-template name="error">
				<xsl:with-param name="m">
					<xsl:text>urn:x-safire.ac.za:participation-agreementn should be true/false, not '</xsl:text>
					<xsl:value-of select="saml:AttributeValue[1]"/>
					<xsl:text>'</xsl:text>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

	<!-- hidden has a controlled vocabulary -->
	<xsl:template match="mdattr:EntityAttributes/saml:Attribute[@Name='urn:x-safire.ac.za:hidden']">
		<xsl:variable name="allowed" select="'yes|no|true|false'"/>
		<xsl:if test="not(contains(concat('|', $allowed, '|'), concat('|', saml:AttributeValue[1], '|')))">
			<xsl:call-template name="error">
				<xsl:with-param name="m">
					<xsl:text>urn:x-safire.ac.za:hidden should be true/false, not '</xsl:text>
					<xsl:value-of select="saml:AttributeValue[1]"/>
					<xsl:text>'</xsl:text>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

	<!-- federation-tag -->
	<xsl:template match="mdattr:EntityAttributes/saml:Attribute[@Name='urn:x-safire.ac.za:federation-tag']">
		<!-- TODO -->
	</xsl:template>

	<!-- consent-disable has a controlled vocabulary/JSON -->
	<xsl:template match="mdattr:EntityAttributes/saml:Attribute[@Name='urn:x-safire.ac.za:consent-disable']">
		<xsl:variable name="allowed" select="'yes|no|true|false'"/>
		<xsl:if test="not(contains(concat('|', $allowed, '|'), concat('|', saml:AttributeValue[1], '|')))">
			<xsl:if test="php:functionString('xsltfunc::checkJSON',saml:AttributeValue[1],'array') = 0">
				<xsl:call-template name="error">
					<xsl:with-param name="m">
						<xsl:text>urn:x-safire.ac.za:consent-disable should be true/false/JSON array, not '</xsl:text>
						<xsl:value-of select="saml:AttributeValue[1]"/>
						<xsl:text>'</xsl:text>
					</xsl:with-param>
				</xsl:call-template>
			</xsl:if>
		</xsl:if>
	</xsl:template>

	<!-- purpose-is-to has a maximum length -->
	<xsl:template match="mdattr:EntityAttributes/saml:Attribute[@Name='urn:x-safire.ac.za:purpose-is-to']">
		<xsl:if test="string-length(saml:AttributeValue[1]) > 200">
			<xsl:call-template name="error">
				<xsl:with-param name="m">
					<xsl:text>urn:x-safire.ac.za:purpose-is-to should be less than 200 characters (currently </xsl:text>
					<xsl:value-of select="string-length(saml:AttributeValue[1])"/>
					<xsl:text> characters)</xsl:text>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

	<!--
	     These tests both anchor to md:EntityDescriptor, and will both match for
	     IDPSSODescriptors, so it is easier to do them within one template
	 -->
	<xsl:template match="md:EntityDescriptor">

		<!-- organizationName should match Organization -->
		<xsl:if test="md:Organization">
			<xsl:variable name="eao" select="md:Extensions/mdattr:EntityAttributes/saml:Attribute[@Name='urn:x-safire.ac.za:organizationName']/saml:AttributeValue[1]"/>
			<xsl:variable name="oon" select="md:Organization/md:OrganizationName[@xml:lang='en']"/>
			<xsl:if test="$eao and $oon and $eao != $oon">
				<xsl:call-template name="warning">
					<xsl:with-param name="m">
						<xsl:text>urn:x-safire.ac.za:organizationName of '</xsl:text>
						<xsl:value-of select="$eao"/>
						<xsl:text>' does not match md:OrganizationName of '</xsl:text>
						<xsl:value-of select="$oon"/>
						<xsl:text>'</xsl:text>
					</xsl:with-param>
				</xsl:call-template>
			</xsl:if>
		</xsl:if>

		<!-- schacHomeOrganization should match the first scoping element -->
		<xsl:if test="md:IDPSSODescriptor">
			<xsl:variable name="sho" select="md:Extensions/mdattr:EntityAttributes/saml:Attribute[@Name='urn:x-safire.ac.za:schacHomeOrganization']/saml:AttributeValue[1]"/>
			<xsl:variable name="scope" select="md:IDPSSODescriptor/md:Extensions/shibmd:Scope"/>
			<xsl:choose>
				<xsl:when test="$sho and not($scope[text()=$sho])">
					<xsl:call-template name="error">
						<xsl:with-param name="m">
							<xsl:text>urn:x-safire.ac.za:schacHomeOrganization of '</xsl:text>
							<xsl:value-of select="$sho"/>
							<xsl:text>' is not found in any md:Scope</xsl:text>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:when>
				<xsl:when test="$sho and $scope and $sho != $scope[1]">
					<xsl:call-template name="warning">
						<xsl:with-param name="m">
							<xsl:text>urn:x-safire.ac.za:schacHomeOrganization of '</xsl:text>
							<xsl:value-of select="$sho"/>
							<xsl:text>' does not match first md:Scope of '</xsl:text>
							<xsl:value-of select="$scope[1]"/>
							<xsl:text>'</xsl:text>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:when>
			</xsl:choose>
		</xsl:if>

		<!-- now process child elements -->
		<xsl:apply-templates/>
	</xsl:template>

</xsl:stylesheet>
