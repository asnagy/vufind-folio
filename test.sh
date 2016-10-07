curl -w '\n' \
     -D - \
     -H "Content-type: application/json"   \
     -H "X-Okapi-Tenant: andrew"   \
     -H "Authorization: andrew"   \
     http://localhost:8084/apis/bibs?limit=100&offset=0
