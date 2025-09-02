Testing Overview for BSAwesome Favourites System
1. User Authentication States
1.1 Guest Users (Not Logged In)
- Add product to favourites
- Remove product from favourites
- View favourites count in header badge
- Access favourites page with guest view enabled
- Access favourites page with guest view disabled
- Clear all guest favourites
- Session persistence across page reloads
- Session persistence across browser tabs
1.2 Logged In Users
- Add product to favourites (database storage)
- Remove product from favourites
- View favourites count in header badge
- Access favourites page
- Clear all favourites
- Favourites persistence across sessions
- Favourites caching functionality
2. Session Migration on Login
2.1 Guest to User Migration
- Guest has favourites → Login → Favourites migrate to database
- Guest view permissions