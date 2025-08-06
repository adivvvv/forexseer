// resources/js/bootstrap.ts
import Echo from 'laravel-echo'
// import the v2 client from the package entrypoint:
import io from 'socket.io-client'

// make the client available globally for Echo to pick up
;(window as any).io = io

// tell Echo exactly which client to use
;(window as any).Echo = new Echo({
  broadcaster: 'socket.io',
  client:     io,
  host:       `${window.location.protocol}//${window.location.hostname}:6001`,
  path:       '/socket.io',
  transports: ['websocket'],
  forceNew:   true,
  secure:     false,
})