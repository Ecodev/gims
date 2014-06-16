from livereload import Server, shell

server = Server()

# run a shell command
server.watch('*.rst', 'make html')
server.watch('content/*', 'make html')

server.serve(root='_build/html')
