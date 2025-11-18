#!/bin/bash
# Wait for the hot file to be created by Vite
while [ ! -f public/hot ]; do
    sleep 0.5
done

# Give Vite a moment to finish writing
sleep 1

# Update the hot file with the correct IP
echo "http://192.168.68.50:5173" > public/hot
echo "Hot file updated for mobile access"
