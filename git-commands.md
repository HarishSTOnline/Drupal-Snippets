# Git Commands

## Get the stats
```
git apply --stat patch_name.patch
```

## Check the patch
```
git apply --check patch_name.patch
```

## Apply Patch
```
git apply patch_name.patch
```

## Create patch
Stage all your changes and run the command below:
```
git diff --cached  > patch_name.patch
```

