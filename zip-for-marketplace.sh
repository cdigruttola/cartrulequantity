rm -rf tmp
mkdir -p tmp/cartrulequantities
cp -R classes tmp/cartrulequantities
cp -R controllers tmp/cartrulequantities
cp -R config tmp/cartrulequantities
cp -R docs tmp/cartrulequantities
cp -R override tmp/cartrulequantities
cp -R sql tmp/cartrulequantities
cp -R src tmp/cartrulequantities
cp -R translations tmp/cartrulequantities
cp -R views tmp/cartrulequantities
cp -R upgrade tmp/cartrulequantities
cp -R vendor tmp/cartrulequantities
cp -R index.php tmp/cartrulequantities
cp -R logo.png tmp/cartrulequantities
cp -R cartrulequantities.php tmp/cartrulequantities
cp -R config.xml tmp/cartrulequantities
cp -R LICENSE tmp/cartrulequantities
cp -R README.md tmp/cartrulequantities
cd tmp && find . -name ".DS_Store" -delete
zip -r cartrulequantities.zip . -x ".*" -x "__MACOSX"
