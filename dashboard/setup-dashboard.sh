#!/bin/bash

###############################################################################
# NAP Dashboard Setup Script
# 
# This script automates the installation and configuration of the NAP Dashboard
# including Elasticsearch, Logstash, and Grafana using Docker Compose.
#
# Prerequisites:
# - Docker and Docker Compose installed
# - Python 3.7+ installed
# - Run this script from the nap-policy-management/dashboard directory
###############################################################################

set -e  # Exit on any error

# Color output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
ELASTICSEARCH_HOST="${ELASTICSEARCH_HOST:-localhost}"
TIMEZONE="${TZ:-America/New_York}"

echo -e "${GREEN}==================================================${NC}"
echo -e "${GREEN}NAP Dashboard Setup Script${NC}"
echo -e "${GREEN}==================================================${NC}"
echo ""

# Check if we're in the right directory
if [[ ! -f "docker-compose.yaml" ]]; then
    echo -e "${RED}Error: docker-compose.yaml not found!${NC}"
    echo "Please run this script from the nap-policy-management/dashboard directory"
    exit 1
fi

###############################################################################
# Step 1: Clone the repo (optional - commented out since we're already here)
###############################################################################
# echo -e "${YELLOW}Step 1: Cloning repository...${NC}"
# git clone https://github.com/f5devcentral/nap-policy-management
# cd nap-policy-management/dashboard

###############################################################################
# Step 2: Install Elasticsearch-Logstash using docker-compose
###############################################################################
echo -e "${YELLOW}Step 2: Starting Docker containers with timezone ${TIMEZONE}...${NC}"
TZ="${TIMEZONE}" docker compose up -d

echo -e "${GREEN}Waiting for Elasticsearch to be ready...${NC}"
sleep 30  # Give services time to start

# Wait for Elasticsearch to be healthy
MAX_RETRIES=30
RETRY_COUNT=0
until curl -s "http://${ELASTICSEARCH_HOST}:9200/_cluster/health" > /dev/null 2>&1; do
    RETRY_COUNT=$((RETRY_COUNT + 1))
    if [[ $RETRY_COUNT -ge $MAX_RETRIES ]]; then
        echo -e "${RED}Error: Elasticsearch did not start in time${NC}"
        exit 1
    fi
    echo "Waiting for Elasticsearch... (${RETRY_COUNT}/${MAX_RETRIES})"
    sleep 2
done
echo -e "${GREEN}✓ Elasticsearch is ready${NC}"

###############################################################################
# Step 3: Configure Elasticsearch
###############################################################################
echo ""
echo -e "${YELLOW}Step 3: Configuring Elasticsearch...${NC}"

# 3.1 Create signature index
echo "Creating signature index..."
RESPONSE=$(curl -s -X PUT "http://${ELASTICSEARCH_HOST}:9200/signatures/")
echo "$RESPONSE"
if [[ "$RESPONSE" == *"acknowledged"*"true"* ]]; then
    echo -e "${GREEN}✓ Signature index created${NC}"
else
    echo -e "${RED}✗ Failed to create signature index${NC}"
fi

# 3.2 Create index mapping for signature index
echo ""
echo "Creating index mapping for signature index..."
RESPONSE=$(curl -s -d "@elastic/signature-mapping.json" -H 'Content-Type: application/json' -X PUT "http://${ELASTICSEARCH_HOST}:9200/signatures/_mapping/")
echo "$RESPONSE"
if [[ "$RESPONSE" == *"acknowledged"*"true"* ]]; then
    echo -e "${GREEN}✓ Index mapping created${NC}"
else
    echo -e "${RED}✗ Failed to create index mapping${NC}"
fi

# 3.3 Populate signature index
echo ""
echo "Populating signature index (this may take ~1 minute)..."
if [[ -f "signatures/signatures-report.json" ]]; then
    python3 signatures/upload-signatures.py signatures/signatures-report.json "${ELASTICSEARCH_HOST}"
    echo -e "${GREEN}✓ Signatures uploaded${NC}"
else
    echo -e "${RED}Warning: signatures/signatures-report.json not found${NC}"
    echo "You'll need to generate this file using the NGINX attack signature report tool"
fi

# 3.4 Create ILM policy to delete WAF indices older than 60 days
echo ""
echo "Creating ILM policy for WAF indexes (delete after 60 days)..."
RESPONSE=$(curl -s -d "@elastic/ilm-policy.json" -H 'Content-Type: application/json' -X PUT "http://${ELASTICSEARCH_HOST}:9200/_ilm/policy/waf-ilm-policy")
echo "$RESPONSE"
if [[ "$RESPONSE" == *"acknowledged"*"true"* ]]; then
    echo -e "${GREEN}✓ ILM policy created${NC}"
else
    echo -e "${RED}✗ Failed to create ILM policy${NC}"
fi

# 3.5 Create template for NAP indexes
echo ""
echo "Creating template for NAP indexes..."
RESPONSE=$(curl -s -d "@elastic/template-mapping.json" -H 'Content-Type: application/json' -X PUT "http://${ELASTICSEARCH_HOST}:9200/_template/waf_template?include_type_name")
echo "$RESPONSE"
if [[ "$RESPONSE" == *"acknowledged"*"true"* ]]; then
    echo -e "${GREEN}✓ Template created${NC}"
else
    echo -e "${RED}✗ Failed to create template${NC}"
fi

# 3.6 Create enrich policy
echo ""
echo "Creating enrich policy for NAP/Signatures indices..."
RESPONSE=$(curl -s -d "@elastic/enrich-policy.json" -H 'Content-Type: application/json' -X PUT "http://${ELASTICSEARCH_HOST}:9200/_enrich/policy/signatures-policy")
echo "$RESPONSE"
if [[ "$RESPONSE" == *"acknowledged"*"true"* ]]; then
    echo -e "${GREEN}✓ Enrich policy created${NC}"
else
    echo -e "${RED}✗ Failed to create enrich policy${NC}"
fi

# 3.7 Deploy enrich policy
echo ""
echo "Deploying enrich policy..."
RESPONSE=$(curl -s -X POST "http://${ELASTICSEARCH_HOST}:9200/_enrich/policy/signatures-policy/_execute")
echo "$RESPONSE"
if [[ "$RESPONSE" == *"COMPLETE"* ]]; then
    echo -e "${GREEN}✓ Enrich policy deployed${NC}"
else
    echo -e "${RED}✗ Failed to deploy enrich policy${NC}"
fi

# 3.8 Create ingest pipeline
echo ""
echo "Creating ingest pipeline..."
RESPONSE=$(curl -s -d "@elastic/sig-lookup.json" -H 'Content-Type: application/json' -X PUT "http://${ELASTICSEARCH_HOST}:9200/_ingest/pipeline/sig_lookup")
echo "$RESPONSE"
if [[ "$RESPONSE" == *"acknowledged"*"true"* ]]; then
    echo -e "${GREEN}✓ Ingest pipeline created${NC}"
else
    echo -e "${RED}✗ Failed to create ingest pipeline${NC}"
fi

###############################################################################
# Final Summary
###############################################################################
echo ""
echo -e "${GREEN}==================================================${NC}"
echo -e "${GREEN}Setup Complete!${NC}"
echo -e "${GREEN}==================================================${NC}"
echo ""
echo "Services:"
echo "  - Elasticsearch: http://${ELASTICSEARCH_HOST}:9200"
echo "  - Logstash:      listening on port 8515"
echo "  - Grafana:       http://${ELASTICSEARCH_HOST}:3000"
echo ""
echo "Next Steps:"
echo "  1. Configure your NAP logging profile (see README.md)"
echo "  2. Point NAP logs to Logstash at ${ELASTICSEARCH_HOST}:8515"
echo "  3. Access Grafana at http://${ELASTICSEARCH_HOST}:3000"
echo "     - Default credentials: admin/admin"
echo ""
echo "To view logs:"
echo "  docker compose logs -f"
echo ""
echo "To stop services:"
echo "  docker compose down"
echo ""
