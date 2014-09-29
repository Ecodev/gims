require 'bootstrap-sass'
require 'autoprefixer-rails'


# Set this to the root of your project when deployed:
http_path = "/"
css_dir = "htdocs/css"
sass_dir = "module/Application/sass"
images_dir = "htdocs/img"
javascripts_dir = "htdocs/js"
cache_dir = "data/cache/sass"

# You can select your preferred output style here (can be overridden via the command line):
# output_style = :expanded or :nested or :compact or :compressed

# To enable relative paths to assets via compass helper functions. Uncomment:
# relative_assets = true

# To disable debugging comments that display the original location of your selectors. Uncomment:
# line_comments = false


# If you prefer the indented syntax, you might want to regenerate this
# project again passing --syntax sass, or you can uncomment this:
# preferred_syntax = :sass
# and then run:
# sass-convert -R --from scss --to sass sass scss && rm -rf sass && mv scss sass

# Enable autoprefixer after compass processing
on_stylesheet_saved do |file|
  css = File.read(file)
  File.open(file, 'w') do |io|
    io << AutoprefixerRails.process(css)
  end
end
